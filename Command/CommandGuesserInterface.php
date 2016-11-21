<?php

namespace Zemd\Component\Micro\Command;

use Symfony\Component\HttpFoundation\Request;

interface CommandGuesserInterface
{
  public function guess(Request $request);
}
