<?php

namespace Zemd\Component\Micro\Handler\Meta;

abstract class AbstractCommandHandlerMeta implements HandlerMetaInterface
{
  public function getCommandGuesser() {
    return false;
  }
}
