<?php namespace Vankosoft\WebsocketBundle\Websocket\Connection;

use React\Async;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use React\Promise\Promise;
use Amp\DeferredCancellation;

use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketClientInterface;

trait ReactFunctions
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
    
    public function setReactConnectAiOpponent( WebsocketClientInterface $socket ): void
    {
        $promise = $this->ReactAsync( [$this, 'ConnectAiOpponent'], $socket );
        Async\await( $promise );
    }
    
    public function setReactNewTurn( WebsocketClientInterface $socket ): void
    {
        $promise = $this->ReactAsync( [$this, 'NewTurn'], $socket );
        Async\await( $promise );
    }
    
    public function setEngineRolledAction( WebsocketClientInterface $client ): void
    {
        $promise = $this->ReactAsync( [$this, 'sendRolledAction'], $client );
        Async\await( $promise );
    }
    
    public function ReactAsync( callable $function, $args ): Promise
    {
        $promise = Async\async( function () use ( $function, $args ) {
            \call_user_func( $function, $args );
        })();
        
        return $promise;
    }
    
    public function ReactAsyncDelay( float $seconds ): void
    {
        Async\delay( $seconds );
    }
}