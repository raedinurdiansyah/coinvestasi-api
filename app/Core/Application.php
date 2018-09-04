<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

use ArrayIterator\Coinvestasi\Core\Exception\UnauthorizedException;
use ArrayIterator\Coinvestasi\Core\Handler\Unauthorized;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Exception\SlimException;

/**
 * Class Application
 * @package ArrayIterator\Coinvestasi\Core
 */
class Application extends \Slim\App
{
    /**
     * Call relevant handler from the Container if needed. If it doesn't exist,
     * then just re-throw.
     *
     * @param  \Exception $e
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws \Exception if a handler is needed and not found
     */
    protected function handleException(
        \Exception $e,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        if ($e instanceof MethodNotAllowedException) {
            $handler = 'notAllowedHandler';
            $params = [$e->getRequest(), $e->getResponse(), $e->getAllowedMethods()];
        } elseif ($e instanceof NotFoundException) {
            $handler = 'notFoundHandler';
            $params = [$e->getRequest(), $e->getResponse(), $e];
        } elseif ($e instanceof UnauthorizedException) {
            $obj = new Unauthorized();
            return $obj($e->getRequest(), $e->getResponse());
        } elseif ($e instanceof SlimException) {
            // This is a Stop exception and contains the response
            return $e->getResponse();
        } else {
            // Other exception, use $request and $response params
            $handler = 'errorHandler';
            $params = [$request, $response, $e];
        }

        if ($this->getContainer()->has($handler)) {
            $callable = $this->getContainer()->get($handler);
            // Call the registered handler
            return call_user_func_array($callable, $params);
        }

        // No handlers found, so just throw the exception
        throw $e;
    }
}