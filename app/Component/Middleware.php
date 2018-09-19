<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Component;

use ArrayIterator\Coinvestasi\Core\Application;
use ArrayIterator\Coinvestasi\Core\Hook;
use ArrayIterator\Extension\Loader;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as R;
use Psr\Http\Message\ServerRequestInterface as S;
use Slim\Http\Environment;
use Slim\Http\Request;
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

    array_map(
        [$this['extension'], 'load'],
        $this['extension']->getAllAvailableExtensions()
    );
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
        )
    );
});
