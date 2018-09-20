<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\Conferences;

use ArrayIterator\Coinvestasi\Core\AddOnExtension;
use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Extension\ExtensionInfo;

/**
 * Class Conferences
 * @package ArrayIterator\Coinvestasi\Extensions\Conferences
 */
class Conferences extends AddOnExtension
{
    /**
     * @var string
     */
    protected $extensionName = 'Conferences';

    /**
     * @var string
     */
    protected $extensionDescription = 'Conferences API';

    /**
     * @param ExtensionInfo $info
     */
    protected function onAfterConstruct(ExtensionInfo $info)
    {
    }

    public function addRouteGroup(Application $a)
    {
    }

    /**
     * Prepare Construct
     */
    protected function prepare()
    {
        $this->registerObjectAutoloader();
    }
}
