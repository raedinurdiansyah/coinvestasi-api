<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\NotAllowed as SlimNotAllowed;

/**
 * Class NotAllowed
 * @package ArrayIterator\Coinvestasi\Core\Handler
 */
class NotAllowed extends SlimNotAllowed
{
    /**
     * @var int
     */
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
     * Render JSON not allowed message
     *
     * @param  array                  $methods
     * @return string
     */
    protected function renderJsonNotAllowedMessage($methods)
    {
        return json_encode([
            'error' => [
                'message' => sprintf(
                    "Method not allowed. Must be one of: %s",
                    implode(', ', $methods)
                )
            ],
            $this->option
        ]);
    }
}
