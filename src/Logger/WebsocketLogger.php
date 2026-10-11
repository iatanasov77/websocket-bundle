<?php namespace Vankosoft\WebsocketBundle\Logger;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
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
    
    public function __construct( string $logChannel, string $logFile, string $environement )
    {
        $this->environement = $environement;
        
        $this->logger = new Logger( $logChannel );
        $this->logger->pushHandler( new StreamHandler( $logFile, Level::Info ) );
    }
    
    public function log( string $logData, ?string $context = null ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( \sprintf( "[Test Websocket] %s", $logData ) );
        }
    }
}
