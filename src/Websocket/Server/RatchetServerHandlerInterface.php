<?php namespace Vankosoft\WebsocketBundle\Websocket\Server;

use Ratchet\MessageComponentInterface;
use Vankosoft\WebsocketBundle\Websocket\Connection\RatchetConnectionInterface;

/**
 * Ported From Ratchet\MessageComponentInterface
 */
interface RatchetServerHandlerInterface extends MessageComponentInterface
{
    /**
     * When a new connection is opened it will be passed to this method
     * @param  RatchetConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen( RatchetConnectionInterface $conn );
    
    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  RatchetConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose( RatchetConnectionInterface $conn );
    
    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  RatchetConnectionInterface $conn
     * @param  \Exception          $e
     * @throws \Exception
     */
    function onError( RatchetConnectionInterface $conn, \Exception $e );
    
    /**
     * Triggered when a client sends data through the socket
     * @param  RatchetConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string                       $msg  The message received
     * @throws \Exception
     */
    function onMessage( RatchetConnectionInterface $from, $msg );
}