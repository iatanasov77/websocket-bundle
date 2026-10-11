<?php namespace Vankosoft\WebsocketBundle\Websocket\Server;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use SplObjectStorage as SplObjectStorageAlias;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Vankosoft\WebsocketBundle\Websocket\WebsocketClientFactory;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;

/**
 * Manual: https://gaurangdangi.medium.com/building-a-web-socket-server-in-laravel-with-ratchet-ff2f10385d03
 */
class RatchetServerHandler implements MessageComponentInterface
{
    /** @var MyLoggerInterface */
    protected $logger;
    
    /** @var SerializerInterface */
    protected $serializer;
    
    /** @var bool */
    protected $logExceptionTrace;
    
    /** @var string */
    protected $websocketUrl;
    
    /** @var WebsocketClientFactory */
    protected $wsClientFactory;
    
    /** @var int */
    protected $connectionSequenceId;
    
    /** @var SplObjectStorageAlias */
    protected $clients;
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        bool $logExceptionTrace,
        string $websocketUrl,
        WebsocketClientFactory $wsClientFactory
    ) {
        $this->logger               = $logger;
        $this->serializer           = $serializer;
        $this->logExceptionTrace    = $logExceptionTrace;
        $this->websocketUrl         = $websocketUrl;
        $this->wsClientFactory      = $wsClientFactory;
        
        $this->connectionSequenceId = 0;
        $this->clients              = new SplObjectStorageAlias();
    }
    
    public function onOpen( ConnectionInterface $conn )
    {
        $origin = isset( $headers['Origin'] ) ? $headers['Origin'] : '';
        if ( $origin !== '' && $origin !== 'http://localhost' ) {
            $conn->close();
        }
        $this->clients->attach( $conn );
        echo "New connection: ({$conn->resourceId})\n";
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
    {
        if ( \is_string( $msg ) ) {
            $response = $msg;
            $from->send( $response );
        } else {
            echo "Received a non-string message\n";
        }
        
        foreach ( $this->clients as $client ) {
            if ( $from !== $client ) {
                $client->send( "User {$from->resourceId} says: {$msg}" );
            }
        }
    }
    
    public function onClose( ConnectionInterface $conn )
    {
        $this->clients->detach( $conn );
        echo "Connection {$conn->resourceId} closed\n";
    }
    
    public function onError( ConnectionInterface $conn, \Exception $e )
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}