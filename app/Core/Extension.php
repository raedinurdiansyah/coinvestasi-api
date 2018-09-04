<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

use ArrayIterator\Extension\Extension as ArrayExtension;
use ArrayIterator\Extension\ExtensionInfo;

/**
 * Class Extension
 * @package ArrayIterator\Coinvestasi\Core
 */
class Extension extends ArrayExtension
{
    /**
     * @var ApiGroup
     */
    protected $api;

    /**
     * {@inheritdoc}
     */
    final public function __construct(
        ExtensionInfo $info,
        ApiGroup $api = null
    ) {
        if (!$api) {
            throw new \InvalidArgumentException(
                'Argument api can not be empty'
            );
        }
        $this->api = $api;
        parent::__construct($info);
    }


    /**
     * Register Autoloader
     */
    protected function registerObjectAutoloader()
    {
        $class = get_class($this);
        $nameSpace = preg_replace('/^(.+)\\\[^\/]+$/', '$1', $class);
        $autoload = require  __DIR__ .'/../../vendor/autoload.php';
        $autoload->addPsr4(
            $nameSpace .'\\',
            [dirname($this->extensionInfo->getClassPath())]
        );
    }

    /**
     * @return ApiGroup
     */
    public function getApi() : ApiGroup
    {
        return $this->api;
    }
}
