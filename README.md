# Micro

> Lightweight domain driven rest api architecture

## Installation

```sh
composer require zemd/micro
```

## Usage

This is lightweight approach to manage rest api calls translate them into pretty domain objects and route to handler. Such
approach lets to avoid messy boilerplate code during handling api requests.


So you have some specific code that handles "requests" and returns some data. It is independent code that can be placed 
in rabbitMQ consumer or elsewhere on the net. Let's call it **handler** and define that each such handler must implement
simple method `handle`.

As such `handler` doesn't know about http requests it receives in it's parameter some **command** object. It nothing else 
than serializable object that can be transferred via any transport mechanism without data loss. 

```php
class FeatureHandler implements HandlerInterface {
  public function handle(CommandInterface $command, $context) {
    // do stuff
    return "response";
  }
}
```

To make it accessible to some router that will look up for handlers there need to be passed some **meta** information about
your endpoint and it's destination. In systems where endpoint can be physically be placed in different servers, you can 
share meta information in your target services, and return collected info in instance of **HandlerMetaInterface** interface.

```php
class FeatureMetaInformation implements HandlerMetaInterface {
    public function getMethods() {
      return ['GET'];
    }
  
    // Unique string that match handler name, it should be possible to be constructed from request string
    public function getEndpoint() {
      return '/users/search';
    }
    
    public function getCommandGuesser() {
      return false; // If false returns getCommandClass() method will be used.
    }
    
    public function getCommandClass() {
      return 'my.command.as.service'; // or FeatureCommand::class
    }
  
    public function getDescription() {
      return 'Some cool feature';
    }
}
```

Do not forget to tag your meta provider in **services.yml**
```yml
my.feature.handler.meta.information:
  class: Namespace\To\My\FeatureMetaInformation
  tags:
    - zemd.micro.handler_meta
```

Now we have information and handler, we can continue with building **Command**. Commands are building automatically with
**CommandBuilder**, so you should only define it with passing additional information using annotations and make command
available to builder and handler.  

`@RequestParam` - says that prop's value is defined in request under desired alias, can define custom pattern to validate, 
and set if this parameter is required to build command.

`@Type` - says that prop is defined as object type

You can defined your own annotations if you wish.

```php
  class FeatureCommand implements CommandInterface {
    /**
     * @RequestParam("mobile", pattern="\+\d{8,13}", required=true)
     * @Type("\My\Project\ValueObjects\MobileObject")
     * @Constraints\NotNull()
     */
    protected $mobile;
    
    /**
     * @RequestParam("country_code", required=false)
     * @Type("\My\Project\ValueObjects\CountryInfoObject")
     */
    protected $countryInfo;
    
    /**
     * @RequestParam("lang", required=false)
     */
    protected $lang;
    
    // ... getters/setters/serialize/unserialize
  }
```
As you can see this class is plain domain without any complexity.

So now we have **handler**, **meta** and **command** and we already can launch our command pipe. We can define one controller
action that will be responsible for handling all api calls.

```php
class TestController {
  public function handleAction($param1, $param2, $param3, Request $request) {
    $checkpoint = "$param1/$param2/$param3";
    
    $handlerMetaManager = new HandlerMetaManager();
    $handlerMeta = $handlerMetaManager->getMeta($checkpoint);
    
    $commandBuilder = new CommandBuilder();
    // in case command guesser returns service id, container must be passed to builder
    $commandBuilder->setContainer($container);
    
    $command = $commandBuilder->setRequest($request)
                  ->setMeta($handlerMeta)
                  ->build();
                  
    $handlerDispatcher = new CommandDispatcher($config);
    $result = $handlerDispatcher->dispatch($command);
    
    // now we have $result with response
    // we can serialize it in json/xml/...
    
    return $result;
  }
}
```

## Advanced usage

For now we built simple handler that is placed on the same machine. But what if we want to make it distributed and asynchronous.
You can achieve this by implementing `CommandDispatcherInterface`. By default `CommandDispatcher` supports rabbitmq via 
**old_sound_rabbit_mq** package. This requires to implement you `Consumer`, inside of it you should transfer command to 
handler and return response using rabbitmq xml/rpc mechanism. In such architecture you can implement simple map/reduce handler
or run several heavy computations asynchronously inside handler and then return result back.


## License

Micro is released under the MIT license.

## Donate

[![](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://www.patreon.com/red_rabbit)
[![](https://img.shields.io/badge/flattr-donate-yellow.svg)](https://flattr.com/profile/red_rabbit)
