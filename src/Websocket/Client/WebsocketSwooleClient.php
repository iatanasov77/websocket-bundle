<?php namespace Vankosoft\WebsocketBundle\Websocket\Client;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Psr\Http\Message\UriInterface;

// use Swoole\Http\Client as SwooleClient;
use Swoole\Coroutine\Http\Client as SwooleClient;
use Swoole\WebSocket\Server as WebsocketServer;
use Swoole\WebSocket\Frame as SwooleFrame;
use Swoole\Http\Status as SwooleHttpStatus;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\VSWebsocketException;
use Vankosoft\WebsocketBundle\Psr\InvalidUrlException;
use Vankosoft\WebsocketBundle\Psr\Uri;

/**
 * Manual: https://github.com/lessmore92/swoole-websocket-client/blob/main/src/WebSocketClient.php
 */
final class WebsocketSwooleClient extends AbstractWebsocketClient
{
    /** @var WebsocketServer */
    private $connection;
    
    /** @var SwooleFrame */
    private $frame;
    
    /** @var SwooleClient */
    private $client;
    
    /** @var UriInterface */
    private $uri;
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        string $websocketUrl,
        WebsocketServer $connection
    ) {
        parent::__construct( $logger, $serializer, $websocketUrl );
        
        $this->connection   = $connection;
        $this->client       = $this->createClient( $websocketUrl );
    }
    
    public function send( object $msg ): void
    {
        // , [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT]
        $json   = $this->serializer->serialize( $msg, JsonEncoder::FORMAT );
        
        foreach ( $this->connection->connections as $fd ) {
            if ( $this->connection->isEstablished( $fd ) ) {
                $this->connection->push( $fd, $json );
            }
        }
    }
    
    public function close( int $code ): void
    {
        // $this->connection->close();
        $this->client->close();
    }
    
    public function setFrame( SwooleFrame $frame ): void
    {
        $this->frame = $frame;
    }
    
    private function createClient( string $uri ): SwooleClient
    {
        if ( \is_string( $uri ) ) {
            $this->uri = new Uri( $uri );
        } else if ( \class_implements( $uri, UriInterface::class ) ) {
            $this->uri = $uri;
        } else {
            throw new InvalidUrlException( 'Url must be a string or implemented UriInterface.' );
        }
        
        $host = $this->uri->getHost();
        $port = $this->uri->getPort();
        $ssl  = $this->uri->getScheme() === 'wss';
        if ( empty( $host ) ) {
            throw new InvalidUrlException( 'The WebSocket host should not be empty.' );
        }
        
        if ( empty( $port ) ) {
            $port = $ssl ? 443 : 80;
        }
        
        $client = new SwooleClient( $host, $port, $ssl );
        
        \parse_str( $this->uri->getQuery(), $query );
        $query = \http_build_query( $query );
        
        $path = $this->uri->getPath() ?: '/';
        $path = empty( $query ) ? $path : $path . '?' . $query;
        
        $ret = $client->upgrade( $path );
        if ( ! $ret) {
            if ( $client->errCode !== 0 ) {
                $errCode = $client->errCode;
                $errMsg  = $client->errMsg;
            } else {
                $errCode = $client->statusCode;
                $errMsg  = SwooleHttpStatus::getReasonPhrase( $errCode );
            }
            
            throw new VSWebsocketException( 'Websocket upgrade failed by [' . $errMsg . '(' . $errCode . ')' . '].', $errCode );
        }
        
        return $client;
    }
}
