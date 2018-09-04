<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

use Psr\Container\ContainerInterface;
use Slim\App;

/**
 * Class ApiGroup
 * @package ArrayIterator\Coinvestasi\Core
 * @mixin Application
 */
class ApiGroup
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    protected function prepare(ContainerInterface $container)
    {
        $this->container =& $container;
    }

    /**
     * @param App $slim
     */
    public function __invoke(App $slim)
    {
        $container = $slim->getContainer();
        $container['slim']  = function () use (&$slim) {
            return $slim;
        };
        $c =& $this;
        $container['api'] = function () use (&$c) : ApiGroup {
            return $c;
        };
        $this->prepare($container);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array(
            [
                $this->container['slim'],
                $name
            ],
            $arguments
        );
    }
}
