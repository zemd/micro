<?php

namespace Zemd\Component\Micro\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class ApiAccessible
{
  public $requiredParams;

  public $method;

  public $secured = true;

  public $optionalParams;

  public $description;

  public $restrictedRoles;

  /** @var bool */
  public $pendingPass = false;

  public function getRequiredParams() {
    return $this->explodeParams($this->requiredParams);
  }

  public function getMethod() {
    return $this->method;
  }

  /**
   * @return array | boolean
   */
  public function getOptionalParams() {
    return $this->explodeParams($this->optionalParams);
  }

  /**
   * @return mixed
   */
  public function getRestrictedRoles() {
    return $this->explodeParams($this->restrictedRoles);
  }

  /**
   * @return boolean
   */
  public function isSecured() {
    return (boolean)$this->secured;
  }

  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param string $params
   * @return array
   */
  protected function explodeParams($params) {
    return $params ? array_map('trim', explode(',', $params)) : array();
  }

  /**
   * @return boolean
   */
  public function isPendingPass() {
    return $this->pendingPass;
  }
}
