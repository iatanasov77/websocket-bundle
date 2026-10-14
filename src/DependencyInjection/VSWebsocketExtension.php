<?php namespace Vankosoft\WebsocketBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class VSWebsocketExtension extends Extension
{
    public function load( array $config, ContainerBuilder $container ): void
    {
        $config = $this->processConfiguration( $this->getConfiguration([], $container), $config );
        
        $configDir = new FileLocator( __DIR__ . '/../Resources/config' );
        $loader = new YamlFileLoader( $container, $configDir );
        $loader->load( 'services.yaml' );
        
        $container->setParameter( 'vs_websocket.document_root', $config['document_root'] );
        $container->setParameter( 'vs_websocket.exeption_trace', $config['exeption_trace'] );
        
        $container->setParameter( 'vs_websocket.logger.channel', $config['logger']['channel'] );
        $container->setParameter( 'vs_websocket.logger.path', $config['logger']['path'] );
        
        $container->setParameter( 'vs_websocket.swoole.worker_num_calc', $config['swoole']['worker_num_calc'] );
        $container->setParameter( 'vs_websocket.swoole.worker_num', $config['swoole']['worker_num'] );
    }
}
