<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi;

// Require Bootstrap
use ArrayIterator\Coinvestasi\Core\ApiGroup;
use ArrayIterator\Coinvestasi\Core\Application;

require __DIR__ . '/autoload.php';

return (function () {
    /**
     * @var $this Application
     */
    require __DIR__ . '/Component/Middleware.php';
    $container = $this->getContainer();
    if (isset($container['group'])) {
        unset($container['group']);
    }
    $container['group'] = $this->group('', ApiGroup::class);
    return $this;
})->call(new Application(require __DIR__ . '/Component/Containers.php'));
