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
    
    public function setEngineMove( WebsocketClientInterface $client, $move, &$noMoves ): void
    {
        $promise = Async\async( function () use ( $client, $move, &$noMoves ) {
            $parameters = [
                $client,
                $move,
                &$noMoves
            ];
            \call_user_func_array( [$this, 'sendEngineMove'], $parameters );
        })();
        Async\await( $promise );
    }
    
    public function setEnginePlayCard( WebsocketClientInterface $client, $playCardAction ): void
    {
        $promise = Async\async( function () use ( $client, $playCardAction ) {
            $parameters = [
                $client,
                $playCardAction
            ];
            \call_user_func_array( [$this, 'EnginPlayCardAsync'], $parameters );
        })();
        Async\await( $promise );
    }
    
    public function setReactConnectAiOpponent( WebsocketClientInterface $socket ): void
    {
        $this->ReactAsync( [$this, 'ConnectAiOpponent'], $socket );
    }
    
    public function setReactNewTurn( WebsocketClientInterface $socket ): void
    {
        $this->ReactAsync( [$this, 'NewTurn'], $socket );
    }
    
    public function setEngineRolledAction( WebsocketClientInterface $client ): void
    {
        $this->ReactAsync( [$this, 'sendRolledAction'], $client );
    }
    
    public function ReactAsync( callable $function, $args ): void
    {
        $promise = Async\async( function () use ( $function, $args ) {
            \call_user_func( $function, $args );
        })();
        Async\await( $promise );
    }
    
    public function ReactAsyncDelay( float $seconds ): void
    {
        Async\delay( $seconds );
    }
    
    public function ReactAsyncDelayAwait(  float $seconds  ): void
    {
        $promise = Async\async( function () use ( $seconds )  {
            Async\delay( $seconds );
        })();
        Async\await( $promise );
    }
}