<?php namespace Vankosoft\WebsocketBundle\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use Swoole\WebSocket\Server as WebsocketServer;
use Ratchet\ConnectionInterface as RatchetConnectionInterface;

use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketClientInterface;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketSwooleClient;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketServerClient;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketThruwayClient;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketRatchetConnectionClient;
use Vankosoft\WebsocketBundle\VSWebsocketException;

final class WebsocketClientFactory
{
    const SWOOLE_CLIENT     = 'swoole';
    const RATCHET_CLIENT    = 'ratchet';
    const AMPPHP_CLIENT     = 'amphp';
    const THRUWAY_CLIENT    = 'thruway';
    
    
    
    /** @var SerializerInterface */
    private $serializer;
    
    public function __construct(
        SerializerInterface $serializer,
    ) {
        $this->serializer   = $serializer;
    }
    
    public function createClient( string $clientType, string $url, $connection = null ): WebsocketClientInterface
    {
        switch ( $clientType ) {
            case self::SWOOLE_CLIENT:
                return $this->createSwooleClient( $url, $connection );
                break;
            case self::RATCHET_CLIENT:
                return $this->createRatchetConnectionClient( $url, $connection );
                break;
            case self::AMPPHP_CLIENT:
                return $this->createAmpClient( $url );
                break;
            case self::THRUWAY_CLIENT:
                return $this->createThruwayClient( $url );
                break;
            default:
                throw new VSWebsocketException( "Unknown Websocket Client: '{$clientType}' !" );
        }
    }
    
    /**
     * Using: Swoole\Websocket\Server
     *        https://www.swoole.com/
     */
    private function createSwooleClient( string $url, WebsocketServer $connection )
    {
        if ( ! \extension_loaded( 'swoole' ) && ! \class_exists( WebsocketServer::class ) ) {
            throw new VSWebsocketException( 'Swoole Websocket Client Cannot be Created !!!' );
        }
        
        return new WebsocketSwooleClient( $url, $this->serializer, $connection );
    }
    
    /**
     * Using: Ratchet\Connection
     *        https://github.com/voryx/Thruway.git
     */
    private function createRatchetConnectionClient( string $url, RatchetConnectionInterface $connection )
    {
        if ( ! \interface_exists( RatchetConnectionInterface::class ) ) {
            throw new VSWebsocketException( 'Ratchet Websocket Client Cannot be Created !!!' );
        }
        
        return new WebsocketRatchetConnectionClient( $url, $this->serializer, $connection );
    }
    
    /**
     * Using: Textalk/websocket-php
     *        https://github.com/Textalk/websocket-php
     */
    private function createAmpClient( string $url )
    {
        return new WebsocketServerClient( $url, $this->serializer );
    }
    
    /**
     * Using: Textalk/websocket-php
     *        https://github.com/Textalk/websocket-php
     */
    private function createThruwayClient( string $url )
    {
        return new WebsocketServerClient( $url, $this->serializer );
    }
}