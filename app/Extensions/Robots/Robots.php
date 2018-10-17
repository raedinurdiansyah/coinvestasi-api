<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\Conferences;

use ArrayIterator\Coinvestasi\Core\AddOnExtension;
use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Extension\ExtensionInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Robots
 * @package ArrayIterator\Coinvestasi\Extensions\Conferences
 */
class Robots extends AddOnExtension
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
        $a->any('.txt', function (ServerRequestInterface $request, ResponseInterface $r) {
            $r->getBody()->write(<<<ROBOTS
User-agent: *
Disallow: /

ROBOTS
            );
            return $r->withHeader('Content-Type', 'text/plain;charset=utf8');
        });
    }

    /**
     * Prepare Construct
     */
    protected function prepare()
    {
        $this->registerObjectAutoloader();
    }
}
