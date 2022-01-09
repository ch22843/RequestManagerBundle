<?php

namespace Devl0pr\RequestManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Devl0prRequestManagerExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

//        $configuration = new Configuration();

//        $config = $this->processConfiguration($configuration, $configs);
//        $definition = $container->getDefinition('acme.social.twitter_client');
//        $definition->replaceArgument(0, $config['twitter']['client_id']);
//        $definition->replaceArgument(1, $config['twitter']['client_secret']);

        // you now have these 2 config keys
        // $config['twitter']['client_id'] and $config['twitter']['client_secret']
    }

    public function getNamespace()
    {
        return 'https://devl0pr.com/schema/dic/request_manager';
    }
}