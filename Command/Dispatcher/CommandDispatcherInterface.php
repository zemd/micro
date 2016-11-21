<?php

namespace Zemd\Component\Micro\Command\Dispatcher;

use Zemd\Component\Micro\Command\CommandInterface;

interface CommandDispatcherInterface
{
  /**
   * @param string $endpoint
   * @param CommandInterface $command
   * @return CommandDispatcherResponseInterface
   */
  public function dispatch($endpoint, CommandInterface $command);
}
