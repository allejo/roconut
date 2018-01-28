<?php

namespace AppBundle\DependencyInjection\Compiler;

use AppBundle\MessageLogFilter\MessageLogFilterInterface;
use AppBundle\Service\MessageLogTransformer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MessageLogFilterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Don't continue if this interface isn't defined
        if (!$container->has(MessageLogFilterInterface::class)) {
            return;
        }

        $definition = $container->findDefinition(MessageLogTransformer::class);
        $taggedServices = $container->findTaggedServiceIds(MessageLogFilterInterface::SERVICE_ID);

        foreach ($taggedServices as $id => $taggedService) {
            $definition->addMethodCall('registerMessageFilter', [new Reference($id)]);
        }
    }
}
