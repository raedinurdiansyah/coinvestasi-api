<?php
namespace ArrayIterator\Coinvestasi;

/**
 * Autoloader Case Insensitive
 */
spl_autoload_register(function ($className) {
    static $map;

    $className = strtolower($className);
    if (!isset($map)) {
        $map = (require __DIR__ . '/../vendor/autoload.php')->getClassMap();
        $map = array_change_key_case($map, CASE_LOWER);
    }
    if (isset($map[$className]) && file_exists($map[$className])) {
        /** @noinspection PhpIncludeInspection */
        require $map[$className];
    }
});

return require __DIR__ .'/../vendor/autoload.php';
