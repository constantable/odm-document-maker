<?php

namespace Constantable\OdmDocumentMaker;

use Constantable\OdmDocumentMaker\DependencyInjection\CompilerPass\SetDoctrineODMAnnotatedPrefixesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OdmDocumentMakerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetDoctrineODMAnnotatedPrefixesPass());
    }
}
