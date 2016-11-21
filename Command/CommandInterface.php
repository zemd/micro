<?php

namespace Zemd\Component\Micro\Command;

use Serializable;

/**
 * CAUTION: EACH COMMAND MUST BE SERIALIZABLE
 *
 * Lightweight command class that defines a command is been sent to handler
 */
interface CommandInterface extends Serializable
{

}
