<?php

namespace Zemd\Component\Micro\Command;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Instantiator\Instantiator;
use Exception;
use GeneratedHydrator\Configuration;
use Zemd\Component\Micro\Annotations\RequestParam;
use Zemd\Component\Micro\Annotations\Type;
use Zemd\Component\Micro\Handler\Meta\HandlerMetaInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\RecursiveValidator;

class Builder implements ContainerAwareInterface
{
  use ContainerAwareTrait;

  /** @var Reader */
  protected $reader;

  /** @var Instantiator */
  protected $instantiator;

  /** @var RecursiveValidator */
  protected $validator;

  /** @var HandlerMetaInterface */
  protected $meta;

  /** @var Request */
  protected $request;

  /** @var ParameterBag */
  protected $requestVars;

  /**
   * @param Reader $reader
   */
  public function __construct(Reader $reader) {
    $this->reader = $reader;

    $this->instantiator = new Instantiator();
    $this->validator = Validation::createValidatorBuilder()
      ->enableAnnotationMapping()
      ->getValidator();
  }

  public function setMeta(HandlerMetaInterface $meta) {
    $this->meta = $meta;

    return $this;
  }

  public function setRequest(Request $request) {
    $this->request = $request;
    $this->checkRequestMethod();
    $this->requestVars = $this->getRequestParameters();

    return $this;
  }

  /**
   * @return CommandInterface
   * @throws Exception
   */
  public function buildCommand() {
    $commandClass = new ReflectionClass($this->getCommandClass());
    $properties = $commandClass->getProperties();

    $data = array();
    $commandData = array();
    // TODO: make RequestParam check within iterator
    foreach ($properties as $prop) {
      // if RequestParam annotation was not been set then we do not touch it and value of this prop should be set from other sources
      $hasRequestParam = !is_null($this->reader->getPropertyAnnotation($prop, RequestParam::class));
      if (!$hasRequestParam) {
        continue;
      }

      $propName = $prop->getName();
      $propsAnnotations = $this->reader->getPropertyAnnotations($prop);

      /** @var RequestParam $annotation */
      foreach ($propsAnnotations as $annotation) {
        // TODO: move this check inside iterator
        if (!$annotation instanceof RequestParam) {
          continue;
        }

        $requestName = $annotation->getName();
        if (is_null($requestName)) {
          $requestName = $propName;
        }

        $value = $this->requestVars->get($requestName);
        if (is_null($value) && $annotation->isRequired()) {
          throw new Exception("No required parameter {$annotation->getName()}");
        }

        $pattern = $annotation->getPattern();
        if (isset($pattern) && !preg_match($annotation->getPattern(), $value)) {
          throw new Exception("The parameter " . ($annotation->getName()) . " is not matches the requirements");
        }

        $data[$propName][$annotation->getName()] = $value;
      }

      /** @var Type|null $propType */
      $propType = $this->reader->getPropertyAnnotation($prop, Type::class);
      // allowed only one usage of Type annotation
      $propValue = current($data[$propName]);

      if (!is_null($propType)) {
        switch ($propType->getType()) {
          case 'string':
            //$propValue = $propValue;
            break;
          case 'int':
            $propValue = intval($propValue);
            break;
          case 'boolean':
            $propValue = intval($propValue) === 1;
            break;
          case 'float':
            $propValue = floatval($propValue);
            break;
          case 'array':
            $propValue = is_array($propValue) ? $propValue : array($propValue);
            break;
          default:
            $config = new Configuration($propType->getType());
            $hydratorClass = $config->createFactory()->getHydratorClass();
            $hydrator = new $hydratorClass();
            // TODO: make initialiator customizable via service
            $object = $this->instantiator->instantiate($propType->getType());

            $hydrator->hydrate(
              $data[$propName],
              $object
            );

            $propValue = $object;
        }
      }
      $commandData[$propName] = $propValue;
    }

    $config = new Configuration($commandClass->getName());
    $hydratorClass = $config->createFactory()->getHydratorClass();
    $hydrator = new $hydratorClass();
    // TODO: make initialiator customizable via service
    $command = $this->instantiator->instantiate($commandClass->getName());

    $hydrator->hydrate(
      $commandData,
      $command
    );

    return $command;
  }

  public function build() {
    $command = $this->buildCommand();

    $errors = $this->validator->validate($command);
    if ($errors->count() > 0) {
      throw new Exception("Command validation error: " . var_export($errors, true));
    }

    return $command;
  }

  /**
   * @throws \Exception
   */
  public function checkRequestMethod() {
    if (!in_array($this->request->getMethod(), $this->meta->getMethods())) {
      throw new \Exception(sprintf("Handler is not intended to respond with %s method", $this->request->getMethod()));
    }
  }

  /**
   * @return \Symfony\Component\HttpFoundation\ParameterBag
   */
  public function getRequestParameters() {
    return $this->request->isMethod(Request::METHOD_POST) ? $this->request->request : $this->request->query;
  }

  /**
   * @return string
   * @throws \Exception
   */
  public function getCommandClass() {
    $guesser = $this->meta->getCommandGuesser();
    if ($guesser !== false) {
      if (is_string($guesser)) {
        $guesser = $this->container->get($guesser);
      }

      return $guesser->guess($this->request);
    } else if (is_string($this->meta->getCommandClass())) {
      return $this->meta->getCommandClass();
    }

    throw new \Exception(sprintf("No command class found for endpoint %s", $this->meta->getEndpoint()));
  }
}
