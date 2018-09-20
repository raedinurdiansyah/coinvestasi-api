<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\DummyAPI;

use ArrayIterator\Coinvestasi\Core\AddOnExtension;
use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Coinvestasi\Core\Generator\JsonPatent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DummyAPI
 * @package ArrayIterator\Coinvestasi\Extensions\DummyAPI
 * example object to get api result for :
 * http://domainapi/dummyapi(/.+)?
 */
class DummyAPI extends AddOnExtension
{
    public function addRouteGroup(Application $app)
    {
        // always add slash on first
        $app->any('[/[{params: (?:.+)}[/]]]', [$this, 'routeHandler']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $params
     *
     * @return ResponseInterface
     */
    public function routeHandler(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $params
    ) : ResponseInterface {
        $paths = isset($params['params'])
            ? explode('/', trim(preg_replace('~([\/])+~', '$1', $params['params']), '/'))
            : [];
        return JsonPatent::success($response, [
            'method' => $request->getMethod(),
            'target' => $request->getRequestTarget(),
            'paths' => $paths,
            'query' => $request->getQueryParams()
        ]);
    }
}
