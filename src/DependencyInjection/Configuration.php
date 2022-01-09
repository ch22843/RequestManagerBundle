<?php


namespace Devl0pr\RequestManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('devl0pr_request_manager');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('smart_problem')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pattern')->defaultNull()->example('^/api')->end()
                    ->end()
                ->end()
            ->end()
        ;

//        $treeBuilder->getRootNode()
//            ->children()
//                ->arrayNode('twitter')
//                    ->children()
//                        ->integerNode('client_id')->end()
//                        ->scalarNode('client_secret')->end()
//                    ->end()
//                ->end() // twitter
//            ->end();

        return $treeBuilder;
    }
}