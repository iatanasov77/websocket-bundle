<?php namespace Vankosoft\WebsocketBundle\Websocket\Server;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;

class RatchetServer implements MessageComponentInterface
{
    /** @var MyLoggerInterface */
    protected $logger;
    
    /** @var RatchetServerHandlerInterface */
    protected ?RatchetServerHandlerInterface $serverHandler;
    
    public function __construct(
        MyLoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->serverHandler = null;
    }
    
    public function setHandler( RatchetServerHandlerInterface $handler ): void
    {
        $this->serverHandler = $handler;
    }
    
    public function onOpen( ConnectionInterface $conn )
    {
        $this->serverHandler->onOpen( $conn );
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
    {
        $this->serverHandler->onMessage( $conn, $msg );
    }
    
    public function onClose( ConnectionInterface $conn )
    {
        $this->serverHandler->onClose( $conn );
    }
    
    public function onError( ConnectionInterface $conn, \Exception $e )
    {
        $this->serverHandler->onError( $conn, $e );
    }
}