<?php namespace Vankosoft\WebsocketBundle\Logger;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Psr\Log\LoggerInterface;



use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

/**
 * Manual: https://www.dash0.com/guides/monolog-logging-php
 */
class WebsocketLogger implements MyLoggerInterface
{
    /** @var LoggerInterface */
    protected  $logger;
    
    /** @var string */
    protected $environement;
    
    public function __construct( LoggerInterface $logger, string $environement )
    {
        $this->environement = $environement;
        // $this->logger       = $logger;
        
        
        $this->logger = new Logger( 'test_websocket' );
        $this->logger->pushHandler(
            new StreamHandler( '/dev/shm/game-platform.lh/game-platform/log/test_websocket.log', Level::Info )
        );
    }
    
    public function log( string $logData, ?string $context = null ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( \sprintf( "[Test Websocket] %s", $logData ) );
        }
    }
}
