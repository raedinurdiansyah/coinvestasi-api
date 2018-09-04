<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

use ArrayIterator\Extension\ExtensionInfo;
use ArrayIterator\Extension\ExtensionInterface;
use ArrayIterator\Extension\Loader as ArrayIteratorLoader;
use ArrayIterator\Extension\ParserInterface;
use Psr\Container\ContainerInterface;

/**
 * Class Loader
 * @package ArrayIterator\Coinvestasi\Core
 */
class Loader extends ArrayIteratorLoader
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Loader constructor.
     * @param string $extensionsDirectory
     * @param bool $strictMode
     * @param ParserInterface|null $parser
     * @param ContainerInterface $container
     */
    public function __construct(
        string $extensionsDirectory,
        bool $strictMode = false,
        ParserInterface $parser = null,
        ContainerInterface $container
    ) {
        parent::__construct($extensionsDirectory, $strictMode, $parser);
        $this->container = $container;
    }

    /**
     * @param string $extensionClassName
     * @param ExtensionInfo $info
     * @return ExtensionInterface
     */
    protected function instantiateExtension(
        string $extensionClassName,
        ExtensionInfo $info
    ) : ExtensionInterface {
        if (!isset($this->container['api'])) {
            throw new \RuntimeException(
                'Container api has not found.'
            );
        }
        return new $extensionClassName($info, $this->container['api']);
    }
}
