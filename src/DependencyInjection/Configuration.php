<?php


namespace Devl0pr\RequestManager\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('devl0pr_request_manager');

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