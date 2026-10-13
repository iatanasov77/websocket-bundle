<?php namespace Vankosoft\WebsocketBundle\Websocket\Server;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

interface SwooleServerHandlerInterface
{
    public function onStart( WebsocketServer $server ): void;
    public function onOpen( WebsocketServer $server, Request $request ): void;
    public function onRequest( Request $request, Response $response ): void;
    public function onMessage( WebsocketServer $server, Frame $frame ): void;
    public function onClose( WebsocketServer $server, int $fd ): void;
}