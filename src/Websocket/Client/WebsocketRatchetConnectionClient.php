<?php namespace Vankosoft\WebsocketBundle\Websocket\Client;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Ratchet\ConnectionInterface;

final class WebsocketRatchetConnectionClient extends AbstractWebsocketClient
{
    /** @var ConnectionInterface */
    private $connection;
    
    public function __construct( string $websocketUrl, SerializerInterface $serializer, ConnectionInterface $connection )
    {
        parent::__construct( $websocketUrl, $serializer );
        
        $this->connection   = $connection;
        $this->clientId     = $connection->resourceId;
    }
    
    public function send( object $msg ): void
    {
        $json   = $this->serializer->serialize( $msg, JsonEncoder::FORMAT );
        $this->connection->send( $json );
    }
    
    public function receive(): string
    {
        return '';
    }
    
    public function close( ?int $code = null ): void
    {
        $this->connection->close( $code );
    }
    
    public function subscribe( string $realm, string $topic, \Closure $callback ): void
    {
        
    }
}
