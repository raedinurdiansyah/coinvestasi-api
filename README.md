# API

## CREATING EXTENSION

- Extension must be extend as :
`ArrayIterator\Coinvestasi\Core\AddOnExtension` object class

- Object class extension must be have namespace with class it self
`namespace ArrayIterator\Coinvestasi\Extensions\TheExtensionNameObjectClassDirectory;`

- add method `addRouteGroup` to add application route. The route group is lowercase follow the object class name.

```php
<?php
// always use strict type to code for code quality
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\MyAddOn;

use ArrayIterator\Coinvestasi\Core\AddOnExtension;
use ArrayIterator\Coinvestasi\Core\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MyAddOn
 * @package ArrayIterator\Coinvestasi\Extensions\MyAddOn
 */
class MyAddOn extends AddOnExtension 
{
    /**
     * @param Application $a
     * @return mixed|void
     */
    public function addRouteGroup(Application $a)
    {
        $a->get('/suffix[/]', function(
            ServerRequestInterface $request,
            ResponseInterface $response,
            array $params = []
        ) : ResponseInterface {
            // do with coe
            return $response;
        });
    }
}
```


## RESPONSE


The response must be following JSON Patent Result,

Use `ArrayIterator\Coinvestasi\Core\Generator\JsonPatent` generator to create result.

- Example

```php
<?php
/**
 * @var $a \ArrayIterator\Coinvestasi\Core\Application
 */
$a->any('/pattern', 
    function(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $params
    ) : \Psr\Http\Message\ResponseInterface {
        $data = [
            'token' => 'my-token'
        ];
        return \ArrayIterator\Coinvestasi\Core\Generator\JsonPatent::success(
            $response,
            $data
        );
    }
);
```

## CODE QUALITY

### CODE STYLING

Use [PSR2 on https://www.php-fig.org/psr/psr-2/](https://www.php-fig.org/psr/psr-2/) for code styling.

- after code check with:

```bash
php ./vendor/bin/phpcs
```

- for auto fixer use:

```bash
php ./vendor/bin/phpcbf
```

on working directory

### CODE GUIDE REQUIREMENT

- use php type hinting as possible
- use return type if possible
- use php 7.x or later for all of code
- always use `strict_types` to get better result on object

## NOTE CONFIG

`config.production.php` is prior config override of `config.php`

