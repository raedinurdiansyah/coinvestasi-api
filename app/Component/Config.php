<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Component;

use Apatis\Config\Config;
use Apatis\Config\Factory;

try {
    $config = file_exists(__DIR__ . '/../../config.production.php')
        ? Factory::fromFile(__DIR__ . '/../../config.production.php')
        : (
            file_exists(__DIR__ . '/../../config.php')
            ? Factory::fromFile(__DIR__ . '/../../config.php')
            : new Config()
        );
} catch (\Exception $e) {
    $config = new Config();
}

return $config;