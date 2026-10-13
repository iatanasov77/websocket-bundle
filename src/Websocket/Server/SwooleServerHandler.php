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
        $this->logger->log( "Swoole Server Open Connection: {$request->fd}", 'WebsocketServer' );
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
        
        $webSocket  = $this->wsClientFactory->createClient( WebsocketClientFactory::SWOOLE_CLIENT, $this->websocketUrl, $server );
        $webSocket->State   = WebSocketState::Open;
        
        $server->push( $frame->fd, \json_encode( ["hello", time()] ) );
    }
    
    public function onClose( WebsocketServer $server, int $fd ): void
    {
        $this->logger->log( "Swoole Server Close Connection: {$fd}" );
    }
    
    public function onDisconnect( WebsocketServer $server, int $fd ): void
    {
        $this->logger->log( "Swoole Server Disconnect Connection: {$fd}" );
    }
}
