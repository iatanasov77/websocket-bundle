<?php namespace Vankosoft\WebsocketBundle\Websocket\Client;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Ratchet\ConnectionInterface;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;

final class WebsocketRatchetConnectionClient extends AbstractWebsocketClient
{
    /** @var ConnectionInterface */
    private $connection;
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        string $websocketUrl,
        ConnectionInterface $connection
    ) {
        parent::__construct( $logger, $serializer, $websocketUrl );
        
        $this->connection   = $connection;
        $this->clientId     = $connection->resourceId;
    }
    
    public function send( object $msg ): void
    {
        $json   = $this->serializer->serialize( $msg, JsonEncoder::FORMAT );
        $this->connection->send( $json );
    }
    
    /**
     * Available Codes In: Ratchet\RFC6455\Messaging\Frame
     */
    public function close( int $code ): void
    {
        $this->connection->close( $code );
    }
}
