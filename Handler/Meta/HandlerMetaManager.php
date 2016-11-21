<?php

namespace Zemd\Component\Micro\Handler\Meta;

class HandlerMetaManager
{
  /**
   * @var HandlerMetaInterface[]
   */
  protected $handlers;

  public function addHandlerMeta(HandlerMetaInterface $handlerMeta) {
    $this->handlers[$handlerMeta->getEndpoint()] = $handlerMeta;
  }

  /**
   * @param string $endpoint
   * @return HandlerMetaInterface
   * @throws \Exception
   */
  public function getMeta($endpoint) {
    if (!isset($this->handlers[$endpoint])) {
      throw new \Exception(sprintf("No such endpoint %s registered.", $endpoint));
    }

    return $this->handlers[$endpoint];
  }
}
