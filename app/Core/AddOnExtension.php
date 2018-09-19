<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

use ArrayIterator\Extension\ExtensionInfo;
use Slim\RouteGroup;

/**
 * Class AddOnExtension
 * @package ArrayIterator\Coinvestasi\Core
 * @method mixed addRouteGroup(Application $app)
 */
abstract class AddOnExtension extends Extension
{
    /**
     * @var RouteGroup
     */
    protected $routeGroupObject;

    private $called;

    /**
     * @param ExtensionInfo $info
     */
    final protected function onConstruct(ExtensionInfo $info)
    {
        if ($this->called) {
            return;
        }
        $this->called = true;
        parent::onConstruct($info);
        $this->registerObjectAutoloader();
        if (method_exists($this, 'addRouteGroup')) {
            $this->routeGroupObject = $this->api->group(
                '/'.$this->routeGroupPrefix,
                [$this, 'addRouteGroup']
            );
        }
        $this->onAfterConstruct($info);
    }

    /**
     * @param ExtensionInfo $info
     */
    protected function onAfterConstruct(ExtensionInfo $info)
    {
        // pass
    }
}
