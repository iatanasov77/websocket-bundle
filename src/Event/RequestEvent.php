<?php namespace Vankosoft\WebsocketBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Swoole\Http\Request;
use Swoole\Http\Response;

class RequestEvent extends Event
{
    public const NAME = 'Request';
    
    public function __construct( public readonly Request $request, public readonly Response $response )
    {
        // Nothing
    }
}
