<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\NotFound as SlimNotFound;

/**
 * Class NotFound
 * @package ArrayIterator\Coinvestasi\Core\Handler
 */
class NotFound extends SlimNotFound
{
    protected $option = JSON_PRETTY_PRINT;

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request)
    {
        $optionJson = $request->getQueryParams();
        $this->option = isset($optionJson['compress'])
        && (
            $optionJson['compress'] === 'true'
            || $optionJson['compress'] === '1'
        ) ? 0 : JSON_PRETTY_PRINT;

        return 'application/json';
    }

    /**
     * Return a response for application/json content not found
     *
     * @return string
     */
    protected function renderJsonNotFoundOutput()
    {
        return json_encode([
            'error' => [
                'message' => '404 Not found'
            ]
        ], $this->option);
    }
}
