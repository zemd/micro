<?php

namespace Zemd\Component\Micro\Command\Dispatcher;

use Zemd\Component\Micro\Command\CommandInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CommandDispatcher implements CommandDispatcherInterface, ContainerAwareInterface
{
  use ContainerAwareTrait;

  /**
   * @var array
   */
  protected $clients;

  public function normalizeEndpoint($endpoint) {
    return str_replace('/', '_', $endpoint);
  }

  public function getClient($endpoint) {
    if (isset($this->clients[$endpoint])) {
      return $this->clients[$endpoint];
    }

    return $this->container->get('old_sound_rabbit_mq.' . $this->normalizeEndpoint($endpoint) . '_rpc');
  }

  /**
   * @param string $endpoint
   * @param CommandInterface $command
   * @return CommandDispatcherResponseInterface|void
   */
  public function dispatch($endpoint, CommandInterface $command) {
    $client = $this->getClient($endpoint);
    $client->addRequest(serialize($command), 'test_server', 'request_id');

    // TODO: wrap this call with error handling etc...
    return $client->getReplies();
  }
}
