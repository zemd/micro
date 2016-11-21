<?php

namespace Zemd\Component\Micro\Annotations;

use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class ApiHandler
{
  /**
   * @var string
   * @Enum({"POST", "GET"})
   */
  public $method;

  /** @var string */
  public $description;

  /** @var string */
  public $command;

  /** @var string */
  public $endpoint;

  /**
   * @return string
   */
  public function getMethod() {
    return strtoupper($this->method);
  }

  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @return string
   */
  public function getCommand() {
    return $this->command;
  }

  /**
   * @return string
   */
  public function getEndpoint() {
    return $this->endpoint;
  }
}
