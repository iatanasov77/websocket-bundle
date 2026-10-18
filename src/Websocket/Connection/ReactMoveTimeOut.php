<?php namespace Vankosoft\WebsocketBundle\Websocket\Connection;

use React\Async;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Amp\DeferredCancellation;

trait ReactMoveTimeOut
{
    /**
     * CancellationTokenSource has been renamed to DeferredCancellation in AMPHP Version 3.0.0
     *
     * @var DeferredCancellation
     */
    protected $moveTimeOut;
    
    public function setReactMoveTimeOut(): void
    {
        $this->moveTimeOut = new DeferredCancellation();
        if ( $this->EndGameOnTotalThinkTimeElapse ) {
            Async\async( function () {
                $loop = Loop::get();
                $loop->addPeriodicTimer( 0.5, function ( TimerInterface $timer ) use ( $loop ) {
                    if ( ! $this->moveTimeOut->isCancelled() ) {
                        $this->TimeTick();
                    } else {
                        $loop->cancelTimer( $timer );
                    }
                });
            })();
        }
    }
    
    public function setReactConnectAiOpponent(): void
    {
        $promise = $this->ReactAsync( [$this, 'ConnectAiOpponent'] );
        Async\await( $promise );
    }
    
    public function ReactAsync( callable $function ): callable
    {
        $promise = Async\async( $function );
        
        return $promise;
    }
}