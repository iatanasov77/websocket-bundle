<?php namespace Vankosoft\WebsocketBundle\Websocket\Server;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use SplObjectStorage as SplObjectStorageAlias;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Vankosoft\WebsocketBundle\Websocket\WebsocketClientFactory;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketClientInterface;

class SwooleServerHandler implements SwooleServerHandlerInterface
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
    
    /** @var WebsocketClientInterface */
    protected $socket;
    
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
    
    public function onStart( WebsocketServer $server ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onStart !!!" );
    }
    
    public function onOpen( WebsocketServer $server, Request $request ): void
    {
        $this->logger->log( "Swoole Server Open Connection: {$server->worker_id}", 'WebsocketServer' );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->offsetSet( $server, $this->connectionSequenceId );
        
        $this->socket   = $this->wsClientFactory->createClient( WebsocketClientFactory::SWOOLE_CLIENT, $this->websocketUrl, $server );
        $this->socket->setClientId( $this->connectionSequenceId );
        $this->socket->State   = WebSocketState::Open;
        
        $server->push( $request->fd, json_encode([
            'type' => 'welcome',
            'fd' => $request->fd
        ]));
    }
    
    public function onRequest( Request $request, Response $response ): void
    {
        // Path without query parameters (e.g., /tools/hammers)
        $requestUri = $request->server['request_uri'] ?? '/';
        
        // Path with query parameters if present (e.g., /tools/hammers?price=high)
        $pathInfo = $request->server['path_info'] ?? '/';
        
        // Query string (e.g., price=high)
        $queryString = $request->server['query_string'] ?? '';
        
        // Associative array of GET parameters
        $getParams = $request->get ?? [];
        
        $this->logger->log( "Swoole Server Request Info: {$pathInfo}", 'WebsocketServer' );
        
        $response->end( "URI: " . $requestUri );
    }
    
    public function onMessage( WebsocketServer $server, Frame $frame ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onMessage: {$frame->data}" );
        
        $this->socket->send( ["hello", time()] );
    }
    
    public function onClose( WebsocketServer $server, int $fd ): void
    {
        $this->clients->detach( $server );
        $this->logger->log( "Swoole Server Close Connection: {$server->worker_id}" );
    }
    
    public function onDisconnect( WebsocketServer $server, int $fd ): void
    {
        $this->logger->log( "Swoole Server Disconnect Connection: {$fd}" );
    }
}
