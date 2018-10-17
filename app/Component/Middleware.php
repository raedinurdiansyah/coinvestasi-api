<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Component;

use ArrayIterator\Coinvestasi\Core\ApiGroup;
use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Coinvestasi\Core\Generator\JsonPatent;
use ArrayIterator\Coinvestasi\Core\Hook;
use ArrayIterator\Extension\Loader;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as R;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as S;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

/**
 * @var $this Application
 */
$this->add(function (S $request, R $response, callable $next) {
    /**
     * @var Environment $environment
     * @var ContainerInterface|Hook[]|Loader[] $this
     */
    // continue
    $environment = $this['environment'];
    if (!isset($serverParams['REQUEST_TIME_FLOAT'])) {
        $environment['REQUEST_TIME_FLOAT'] = microtime(true);
        $request = Request::createFromEnvironment($environment);
    }

    $uri = $request->getUri();
    if ($uri instanceof Uri && $environment->get('SCRIPT_NAME') === $uri->getBasePath()) {
        $environment['SCRIPT_NAME'] = dirname($environment['SCRIPT_NAME']);
        $request = $request->withUri($uri->createFromEnvironment($environment));
    }

    if (!preg_match('/^Mozilla\/[0-9](?:\.[0-9])?/', trim($request->getHeaderLine('User-Agent')))
        // set only coinvestasi allow get from ajax
        || $request instanceof Request && $request->isXhr() &&
           !(
                (
                    isset($environment['REMOTE_ADDR']) && $environment['REMOTE_ADDR'] !== '127.0.0.1'
                    || !isset($environment['REMOTE_ADDR'])
                ) && preg_match('/\coinvestasi\.com/', $request->getHeaderLine('Referer'))
           )
    ) {
        /**
         * @var ApiGroup $api
         */
        $api = $this['api'];
        $api->any('{params: .+}', function (ServerRequestInterface $request, ResponseInterface $r) {
            return JsonPatent::errorCode($r, null, 400);
        });
    } else {
        array_map(
            [$this['extension'], 'load'],
            $this['extension']->getAllAvailableExtensions()
        );
    }

    return $next(
        $request->withAddedHeader(
            'Content-Type',
            'application/json;charset=utf-8'
        )->withHeader(
            'Accept',
            'application/json'
        ),
        // no index
        $response->withHeader(
            'Content-Type',
            'application/json;charset=utf-8'
        )->withHeader(
            'X-Robots-Tag',
            'noindex, nofollow, noodp, noydir'
        )->withAddedHeader(
            'Access-Control-Allow-Origin',
            '*'
        )->withAddedHeader(
            'Access-Control-Allow-Methods',
            'POST, OPTIONS, GET'
        )->withAddedHeader(
            'Access-Control-Request-Headers',
            'Content-Type, X-PINGOTHER, Data-Type'
        )
    );
});
