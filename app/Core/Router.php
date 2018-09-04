<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

use Psr\Http\Message\ServerRequestInterface;

class Router extends \Slim\Router
{
    /**
     * Dispatch router for HTTP request
     *
     * @param  ServerRequestInterface $request The current HTTP request object
     *
     * @return array
     *
     * @link   https://github.com/nikic/FastRoute/blob/master/src/Dispatcher.php
     */
    public function dispatch(ServerRequestInterface $request)
    {
        $path = rawurldecode(urldecode($request->getUri()->getPath()));
        $uri = '/' . ltrim($path, '/');

        return $this->createDispatcher()->dispatch(
            $request->getMethod(),
            $uri
        );
    }
}
