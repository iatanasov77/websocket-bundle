<?php namespace Vankosoft\WebsocketBundle\Websocket\Server;

use Ratchet\ConnectionInterface;

/**
 * Ported From Ratchet\MessageComponentInterface
 */
interface RatchetServerHandlerInterface
{
    function onOpen( ConnectionInterface $conn ): void;
    
    function onClose( ConnectionInterface $conn ): void;
    
    function onError( ConnectionInterface $conn, \Exception $e ): void;
    
    function onMessage( ConnectionInterface $from, $msg ): void;
}