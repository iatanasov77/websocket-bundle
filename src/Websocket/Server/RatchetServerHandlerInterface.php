<?php namespace Vankosoft\WebsocketBundle\Websocket\Server;

use Ratchet\ConnectionInterface;

/**
 * Ported From Ratchet\MessageComponentInterface
 */
interface RatchetServerHandlerInterface
{
    public function onOpen( ConnectionInterface $conn ): void;
    
    public function onClose( ConnectionInterface $conn ): void;
    
    public function onError( ConnectionInterface $conn, \Exception $e ): void;
    
    public function onMessage( ConnectionInterface $from, $msg ): void;
}