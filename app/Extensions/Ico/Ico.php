<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\Ico;

use ArrayIterator\Coinvestasi\Core\AddOnExtension;
use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Coinvestasi\Extensions\Ico\Routes\Calendar;
use ArrayIterator\Extension\ExtensionInfo;
use Slim\App;

/**
 * Class Ico
 * @package ArrayIterator\Coinvestasi\Extensions\Ico
 */
class Ico extends AddOnExtension
{
    /**
     * @var string
     */
    protected $extensionName = 'ICO';

    /**
     * @var string
     */
    protected $extensionDescription = 'ICO List API';

    /**
     * @param ExtensionInfo $info
     */
    protected function onAfterConstruct(ExtensionInfo $info)
    {
        $this->prepare();
    }

    /**
     * @param Application $a
     *
     * @return mixed|void
     */
    public function addRouteGroup(Application $a)
    {
        $a->group('', function (App $a) {
            $a->group('/calendar', Calendar::class);
        });
    }

    /**
     *
     */
    protected function prepare()
    {
        $this->registerObjectAutoloader();
    }
}
