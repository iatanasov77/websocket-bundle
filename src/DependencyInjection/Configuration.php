<?php namespace Vankosoft\WebsocketBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder    = new TreeBuilder( 'vs_websocket' );
        $rootNode       = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                
                ->scalarNode( 'document_root' )->defaultValue( '/public' )->cannotBeEmpty()->end()
                ->booleanNode( 'exeption_trace' )->defaultFalse()->end()
                
                ->arrayNode( 'logger' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode( 'channel' )->defaultValue( 'test_websocket' )->cannotBeEmpty()->end()
                        ->scalarNode( 'path' )->defaultValue( '%kernel.logs_dir%/test_websocket.log' )->cannotBeEmpty()->end()
                    ->end()
                ->end()
                
                ->arrayNode( 'swoole' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode( 'worker_num_calc' )->defaultFalse()->end()
                        ->scalarNode( 'worker_num' )->defaultValue( 2 )->cannotBeEmpty()->end()
                    ->end()
                ->end()
                    
            ->end()
        ;
        
        return $treeBuilder;
    }
}
