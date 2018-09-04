<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions;

use ArrayIterator\Coinvestasi\Core\Extension;
use ArrayIterator\Extension\ExtensionInfo;

/**
 * Class Conferences
 * @package ArrayIterator\Coinvestasi\Extensions
 */
class Conferences extends Extension
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
    protected function onConstruct(ExtensionInfo $info)
    {
        parent::onConstruct($info);
        $this->prepare();
    }

    /**
     * Prepare Construct
     */
    protected function prepare()
    {
        $this->registerObjectAutoloader();
    }
}
