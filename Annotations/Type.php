<?php

namespace Zemd\Component\Micro\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Type
{
  public $value;

  public function getType() {
    return $this->value;
  }
}
