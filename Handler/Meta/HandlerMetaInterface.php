<?php

namespace Zemd\Component\Micro\Handler\Meta;

use Zemd\Component\Micro\Command\CommandGuesserInterface;

interface HandlerMetaInterface
{

  /**
   * must return one of the http methods (Request::METHOD_GET or Request::METHOD_POST or etc...)
   *
   * @return array
   */
  public function getMethods();

  /**
   * Unique string that match handler name, it should be possible to be constructed from request string
   *
   * @return string
   */
  public function getEndpoint();

  /**
   * If false returns getCommandClass() method will be used.
   * If string is passed then it means we pass service id and must fetch it from container
   *
   * @return CommandGuesserInterface|false|string
   */
  public function getCommandGuesser();

  /**
   * By default has to return false
   *
   * @return string|false
   */
  public function getCommandClass();

  /**
   * Documentation description string
   *
   * @return string
   */
  public function getDescription();
}
