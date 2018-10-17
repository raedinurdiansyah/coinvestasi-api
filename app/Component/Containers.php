<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Component;

use Apatis\Config\ConfigInterface;
use Apatis\Config\Factory;
use ArrayIterator\Coinvestasi\Core\Cache;
use ArrayIterator\Coinvestasi\Core\Container;
use ArrayIterator\Coinvestasi\Core\DB;
use ArrayIterator\Coinvestasi\Core\Handler\Error;
use ArrayIterator\Coinvestasi\Core\Handler\NotAllowed;
use ArrayIterator\Coinvestasi\Core\Handler\NotFound;
use ArrayIterator\Coinvestasi\Core\Handler\PhpError;
use ArrayIterator\Coinvestasi\Core\Hook;
use ArrayIterator\Coinvestasi\Core\Loader;
use ArrayIterator\Coinvestasi\Core\Router;
use Pentagonal\DatabaseDBAL\Database;
use Psr\Container\ContainerInterface;
use Slim\Handlers\AbstractError;
use Slim\Handlers\AbstractHandler;
use Slim\Interfaces\RouterInterface;

return new Container([
    'settings' => (require __DIR__ .'/Config.php')->toArray(),
    'config'   => function (ContainerInterface $container) : ConfigInterface {
        return Factory::fromArray($container['settings']);
    },
    // Ser Custom Handler to server as JSON Object
    'notFoundHandler' => function () : AbstractHandler {
        return new NotFound();
    },
    'notAllowedHandler' => function () : AbstractHandler {
        return new NotAllowed();
    },
    'errorHandler' => function (ContainerInterface $container) : AbstractError {
        return new Error($container->get('settings')['displayErrorDetails']);
    },
    'phpErrorHandler' => function (ContainerInterface $container) : AbstractError {
        return new PhpError($container->get('settings')['displayErrorDetails']);
    },
    'hook' => function () : Hook {
        return new Hook();
    },
    'cache' => function (ContainerInterface $container) : Cache {
        $settings = $container['settings'];
        if (empty($settings['cache']) || !is_array($settings['cache'])
            || !isset($settings['cache']['driver'])
            || empty($settings['cache']['driver'])
        ) {
            $config = [
                'driver' => Cache::FILE_SYSTEM,
                'path' => __DIR__ .'/../../storage/cache/'
            ];
            if (extension_loaded('redis')) {
                try {
                    $redis = new \Redis();
                    $redis->connect('127.0.0.1');
                    $config = [
                        'driver' => Cache::REDIS,
                        'host' => '127.0.0.1',
                        'redis' => $redis
                    ];
                } catch (\Exception $e) {
                }
            }
        } else {
            $config = $settings['cache'];
            switch ($settings['cache']['driver']) {
                case Cache::SQLITE:
                case 'sqlite':
                case 'sqlite3':
                    $config['dbname'] = __DIR__ .'/../../storage/database/cache.sqlite';
                    if (!isset($config['table']) || !is_string($config['table'])) {
                        $config['table'] = 'cache';
                    }
                    break;
                case Cache::DATABASE:
                case 'database':
                    /**
                     * @var Database[] $container
                     */
                    $config['database'] = $container['db']->getConnection();
                    if (!isset($config['table']) || !is_string($config['table'])) {
                        $config['table'] = 'cache';
                    }
                    break;
            }
        }

        $cache = new Cache($config);
        if ($cache->getDriver() === Cache::ARRAYS
            && is_writable(__DIR__ .'/../../storage/cache/')
        ) {
            $cache = new Cache([
                'driver' => Cache::FILE_SYSTEM,
                'path' => __DIR__ .'/../../storage/cache/'
            ]);
        }
        return $cache;
    },
    'extension' => function (ContainerInterface $container) : \ArrayIterator\Extension\Loader {
        return (new Loader(
            __DIR__ .'/../Extensions/',
            true,
            null,
            $container
        ))->start();
    },
    'db' => function (ContainerInterface $container) : DB {
        $settings = $container['settings'];
        if (isset($settings['db']) && is_array($settings['db'])) {
            $config = $settings['db'];
        } elseif (isset($settings['database']) && is_array($settings['database'])) {
            $config = $settings['database'];
        } else {
            if (!is_dir(__DIR__ .'/../../storage/database')) {
                mkdir(__DIR__ .'/../../storage/database', 0755, true);
            }
            if (!file_exists(__DIR__ .'/../../storage/database/database.sqlite')) {
                touch(__DIR__ .'/../../storage/database/database.sqlite');
            }

            $config = [
                'driver' => 'sqlite',
                'path' => __DIR__ .'/../../storage/database/database.sqlite'
            ];
        }

        $db = DB::instance(new Database($config));
        $db->connect();

        return $db;
    },
    /**
     * This service MUST return a SHARED instance
     * of \Slim\Interfaces\RouterInterface.
     *
     * @param Container $container
     *
     * @return RouterInterface
     */
    'router' => function (ContainerInterface $container) : RouterInterface {
        $routerCacheFile = false;
        if (isset($container->get('settings')['routerCacheFile'])) {
            $routerCacheFile = $container->get('settings')['routerCacheFile'];
        }

        $router = (new Router())->setCacheFile($routerCacheFile);
        if (method_exists($router, 'setContainer')) {
            $router->setContainer($container);
        }

        return $router;
    },
]);
