<?php namespace Vankosoft\WebsocketBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\SocketServer;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Vankosoft\WebsocketBundle\Websocket\Server\RatchetServer;

/**
 * See Logs:        sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_game.log
 * Start Service:   sudo service websocket_game_platform_game restart
 *
 * Forked From: https://www.codeproject.com/Articles/5297405/Online-Backgammon
 * Play Original Game: https://backgammon.azurewebsites.net/
 *
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */

/**
 * Forked From: https://www.codeproject.com/Articles/5297405/Online-Backgammon
 * Play Original Game: https://backgammon.azurewebsites.net/
 */
#[AsCommand(
    name: 'vankosoft:ratchet-websocket:server',
    description: 'Start Ratchet WebSocket Server',
    hidden: false
)]
final class WebsocketRatchetServerCommand extends Command
{
    /** @var ContainerInterface */
    private $container;
    
    /** @var MyLoggerInterface */
    private $websocketLogger;
    
    /** @var array */
    private $parrameters;
    
    /** @var MessageComponentInterface */
    private $serverHandler;
    
    public function __construct(
        ContainerInterface $container,
        MyLoggerInterface $websocketLogger,
        array $parrameters
    ) {
        parent::__construct();
        
        $this->container        = $container;
        $this->websocketLogger  = $websocketLogger;
        $this->parrameters      = $parrameters;
    }
    
    /**
     * possix signal handler function
     */
    public function sigHandler( $signo )
    {
        /**
         * @NOTE POSSIX SIGNAL CODES: https://www.php.net/manual/en/pcntl.constants.php#115603
         */
        switch ( $signo ) {
            case SIGTERM:
                $this->serverHandler->serverWasTerminated();
                exit;
                break;
            case SIGHUP:
                // handle restart tasks
                break;
            default:
                // handle all other signals
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp( 'The <info>%command.name%</info> starts the WebSocket Ratchet Server.' )
            ->addOption( 'handler', '', InputOption::VALUE_OPTIONAL, 'Handler',  'vs_websocket_server_handler' )
            ->addOption( 'host', '', InputOption::VALUE_OPTIONAL, 'Host',  '127.0.0.1' )
            ->addOption( 'port', '', InputOption::VALUE_OPTIONAL, 'Port', 8000 );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        \pcntl_signal( SIGTERM, array( $this, 'sigHandler' ) );
        \pcntl_signal( SIGHUP, array( $this, 'sigHandler' ) );
        
        $handler    = $input->getOption( 'handler' );
        $host       = $input->getOption( 'host' );
        $port       = $input->getOption( 'port' );
        
        $serverHandler          = $this->container->get( $handler );
        $this->serverHandler    = new RatchetServer( $this->websocketLogger );
        $this->serverHandler->setHandler( $serverHandler );
        
        $loop           = EventLoopFactory::create();
        $socketServer   = new SocketServer( "{$host}:{$port}", [
            'local_cert'        => $this->parrameters['sslCertificateCert'],
            'local_pk'          => $this->parrameters['sslCertificateKey'],
            'allow_self_signed' => true,
            'verify_peer'       => false
        ], $loop );
        
        $websocketServer = new IoServer(
            new HttpServer(
                new WsServer(
                    $this->serverHandler
                )
            ),
            $socketServer,
            $loop
        );
        
        $loop->run();
        
        return Command::SUCCESS;
    }
}
