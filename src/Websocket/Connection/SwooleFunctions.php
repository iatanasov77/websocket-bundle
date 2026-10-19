<?php namespace Vankosoft\WebsocketBundle\Websocket\Connection;

trait SwooleFunctions
{
    public function setSwooleMoveTimeOut(): void
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
}