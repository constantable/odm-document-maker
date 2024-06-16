<?php

namespace Constantable\OdmDocumentMaker\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OdmDocumentMakerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $rootNamespace = trim($config['root_namespace'], '\\');

        $doctrineODMHelperDefinition = $container->getDefinition('odm_document_maker.doctrine_odm_helper');
        $doctrineODMHelperDefinition->replaceArgument(0, $rootNamespace.'\\Document');
    }
}
