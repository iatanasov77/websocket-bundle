<?php namespace Vankosoft\WebsocketBundle\Websocket\Client;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

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
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        string $websocketUrl,
        WebsocketServer $connection
    ) {
        parent::__construct( $logger, $serializer, $websocketUrl );
        
        $this->connection   = $connection;
    }
    
    public function send( object $msg ): void
    {
        $json   = $this->serializer->serialize( $msg, JsonEncoder::FORMAT );
        
        foreach ( $this->connection->connections as $fd ) {
            if ( $this->connection->isEstablished( $fd ) ) {
                $this->connection->push( $fd, $json );
            }
        }
    }
    
    public function close( int $code ): void
    {
        // echo "Connection closed: {$fd}\n";
        
        // $this->connection->close();
    }
}
