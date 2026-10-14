<?php namespace Vankosoft\WebsocketBundle\Websocket\Client;

use Symfony\Component\Serializer\SerializerInterface;
use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;

/**
 * WebsocketClient Based on AMPHP
 * ==============================
 * Reference: https://amphp.org/amp
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/60780643/get-websocket-pings-from-an-open-stream-connection-using-amp-websocket
 */
abstract class AbstractWebsocketClient implements WebsocketClientInterface
{
    /** @var MyLoggerInterface */
    protected $logger;
    
    /** @var SerializerInterface */
    protected $serializer;
    
    /** @var string */
    protected $websocketUrl;
    
    /**
     * Ratchet Connection Resource ID or Any Other Websocket Connection Identifier
     * 
     * @var mixed
     */
    protected $clientId;
    
    /** @var WebSocketState */
    public $State;
    
    public function __construct( MyLoggerInterface $logger, SerializerInterface $serializer, string $websocketUrl )
    {
        $this->logger       = $logger;
        $this->serializer   = $serializer;
        
        $this->websocketUrl = $websocketUrl;
        $this->State        = WebSocketState::None;
    }
    
    public function subscribe( string $realm, string $topic, \Closure $callback ): void
    {
        
    }
    
    public function receive(): string
    {
        return '';
    }
    
    public function close( int $code ): void
    {
        
    }
    
    public function getClientId(): mixed
    {
        return $this->clientId;
    }
    
    public function setClientId( mixed $clientId ): void
    {
        $this->clientId = $clientId;
    }
}
