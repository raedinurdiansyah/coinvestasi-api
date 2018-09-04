<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

/**
 * Class Container
 * @package ArrayIterator\Coinvestasi\Core
 */
final class Container extends \Slim\Container
{
    /**
     * @var Container
     */
    private static $instance;

    /**
     * Container constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        self::$instance =& $this;
    }

    /**
     * @return Container
     */
    public static function &instance() : Container
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $containerName
     * @param $content
     *
     * @return Container
     */
    public static function make(
        string $containerName,
        $content
    ) : Container {
        $instance = self::instance();
        $instance[$containerName] = $content;
        return $instance;
    }

    /**
     * @param string $containerName
     *
     * @return mixed
     */
    public static function take(
        string $containerName
    ) {
        $instance = self::instance();
        return $instance[$containerName];
    }

    /**
     * @param string $containerName
     *
     * @return bool
     */
    public static function exist(string $containerName) : bool
    {
        $instance = self::instance();
        return isset($instance[$containerName]);
    }
}
