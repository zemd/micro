<?php

namespace Zemd\Component\Micro\Handler;

use Zemd\Component\Micro\Command\CommandInterface;

interface HandlerInterface
{
  /**
   * @param CommandInterface $command
   * @param $context
   * @return mixed
   */
  public function handle(CommandInterface $command, $context);
}
