<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\Conferences;

use ArrayIterator\Coinvestasi\Core\AddOnExtension;
use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Coinvestasi\Core\Generator\JsonPatent;
use ArrayIterator\Extension\ExtensionInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    /**
     * @param Application $a
     *
     * @return mixed|void
     */
    public function addRouteGroup(Application $a)
    {
        $a->get('[/]', function (ServerRequestInterface $request, ResponseInterface $r) {
            $token = new TokenPal();
            return JsonPatent::success($r, $token->getData());
        });
    }
}
