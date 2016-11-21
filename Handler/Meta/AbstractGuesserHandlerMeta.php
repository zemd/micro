<?php

namespace Zemd\Component\Micro\Handler\Meta;

abstract class AbstractGuesserHandlerMeta implements HandlerMetaInterface
{
  /**
   * @return false
   */
  public function getCommandClass() {
    return false;
  }
}
