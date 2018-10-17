<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\Conferences;

use ArrayIterator\Coinvestasi\Core\AddOnExtension;
use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Coinvestasi\Core\Generator\JsonPatent;
use ArrayIterator\Extension\ExtensionInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Funds extends AddOnExtension
{
    /**
     * @var string
     */
    protected $extensionName = 'Funds';

    /**
     * @var string
     */
    protected $extensionDescription = 'Funds API';

    /**
     * @param ExtensionInfo $info
     */
    protected function onAfterConstruct(ExtensionInfo $info)
    {
    }

    /**
     * @param Application $a
     *
     * @return mixed|void
     */
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
