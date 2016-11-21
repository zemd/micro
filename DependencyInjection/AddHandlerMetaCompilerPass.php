<?php

namespace Zemd\Component\Micro\DependencyInjection;

use InvalidArgumentException;
use Zemd\Component\Micro\Handler\Meta\HandlerMetaInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddHandlerMetaCompilerPass implements CompilerPassInterface
{

  /**
   * You can modify the container here before it is dumped to PHP code.
   *
   * @param ContainerBuilder $container
   */
  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('zemd.micro.handler.meta_manager')) {
      return;
    }

    $metaManagerDefinition = $container->getDefinition('zemd.micro.handler.meta_manager');
    $taggedServices = $container->findTaggedServiceIds('zemd.micro.handler_meta');

    foreach ($taggedServices as $id => $attributes) {
      $handlerMetaDefinition = $container->getDefinition($id);

      $class = $container->getParameterBag()->resolveValue($handlerMetaDefinition->getClass());
      $refClass = new ReflectionClass($class);
      $interface = HandlerMetaInterface::class;

      if (!$refClass->implementsInterface($interface)) {
        throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
      }

      $metaManagerDefinition->addMethodCall(
        'addHandlerMeta',
        [$handlerMetaDefinition]
      );
    }
  }
}
