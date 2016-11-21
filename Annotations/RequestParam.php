<?php

namespace Zemd\Component\Micro\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class RequestParam
{
  /** @var string */
  public $value;

  /** @var string */
  public $pattern;

  /** @var bool */
  public $required = false;

  /**
   * @return string
   */
  public function getName() {
    return $this->value;
  }

  /**
   * @return string
   */
  public function getPattern() {
    if (empty($this->pattern)) {
      return null;
    }

    return '/' . ltrim(trim($this->pattern), '/') . '/';
  }

  /**
   * @return boolean
   */
  public function isRequired() {
    return $this->required;
  }
}
