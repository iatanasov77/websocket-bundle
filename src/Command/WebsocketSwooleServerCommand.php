<?php namespace Vankosoft\WebsocketBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Vankosoft\WebsocketBundle\Websocket\Server\SwooleServer;
use Vankosoft\WebsocketBundle\Websocket\Server\SwooleServerHandlerInterface;
use Vankosoft\WebsocketBundle\VSWebsocketException;

#[AsCommand(
    name: 'vankosoft:swoole-websocket:server',
    description: 'Start Swoole WebSocket Server',
    hidden: false
)]
final class WebsocketSwooleServerCommand extends Command
{
    /** @var ContainerInterface */
    private $container;
    
    /** @var MyLoggerInterface */
    private $websocketLogger;
    
    /** @var string */
    private $documentRoot;
    
    /** @var array */
    private $runtimeOption = [
        // 'socketio' => true,
        'host' => '0.0.0.0',
        'port' => 8000,
        'mode' => SWOOLE_PROCESS,
        'settings' => [],
    ];
    
    /** @var ServerHandlerInteface | null */
    private $serverHandler;
    
    public function __construct(
        ContainerInterface $container,
        MyLoggerInterface $websocketLogger,
        string $documentRoot
    ) {
        parent::__construct();
        
        $this->container = $container;
        
        $this->websocketLogger  = $websocketLogger;
        $this->documentRoot     = $documentRoot;
        
        $workerNum = \swoole_cpu_num() * 2;
        $this->runtimeOption['settings'] = [
            \Swoole\Constant::OPTION_WORKER_NUM => $workerNum,
            \Swoole\Constant::OPTION_ENABLE_STATIC_HANDLER => true,
            \Swoole\Constant::OPTION_DOCUMENT_ROOT => $this->documentRoot,
        ];
    }
    
    public function sigHandler( $signo )
    {
        /**
         * @NOTE POSSIX SIGNAL CODES: https://www.php.net/manual/en/pcntl.constants.php#115603
         */
        switch ( $signo ) {
            case SIGTERM:
                if ( $this->serverHandler ) {
                    $this->serverHandler->serverWasTerminated();
                }
                
                exit;
                break;
            case SIGHUP:
                // handle restart tasks
                break;
            default:
                // handle all other signals
        }
    }

    public function configure()
    {
        $this
            ->setHelp( 'The <info>%command.name%</info> starts the WebSocket Swoole Server.' )
            ->addOption( 'handler', '', InputOption::VALUE_OPTIONAL, 'Handler',  'vs_websocket_server_handler' )
            ->addOption( 'host', '', InputOption::VALUE_OPTIONAL, 'Host',  '127.0.0.1' )
            ->addOption( 'port', '', InputOption::VALUE_OPTIONAL, 'Port', 8000 )
        ;
    }

    public function run( InputInterface $input, OutputInterface $output ): int
    {
        \pcntl_signal( SIGTERM, array( $this, 'sigHandler' ) );
        \pcntl_signal( SIGHUP, array( $this, 'sigHandler' ) );
        
        $options = ['host' => $input->getOption( 'host' ), 'port' => $input->getOption( 'port' )];
        $options = \array_replace_recursive( $this->runtimeOption, $options );
        
        $handler = $input->getOption( 'handler' );
        $this->serverHandler    = $this->container->get( $handler );
        $this->websocketLogger->log( "Swoole Websocket Server Handler: {$handler}"  );
        if ( ! ( $this->serverHandler instanceof SwooleServerHandlerInterface ) ) {
            throw new VSWebsocketException( 'Wrong Swoole Server Handler !!!' );
        }
        
        $server = new SwooleServer( $this->websocketLogger, $options );
        $server->setHandler( $this->serverHandler );
        $server->init();
        $server->setEvent();
        $server->start();
        
        return Command::SUCCESS;
    }
}
