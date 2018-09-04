<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\PhpError as SlimPhpError;

/**
 * Class PhpError
 * @package ArrayIterator\Coinvestasi\Core\Handler
 */
class PhpError extends SlimPhpError
{
    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request)
    {
        return 'application/json';
    }

    /**
     * Render JSON error
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Throwable $exception)
    {
        $error = [
            'error' => [
                'message' => 'Application Error',
            ]
        ];

        if ($this->displayErrorDetails) {
            $error['error']['exception'] = [];

            do {
                $error['error']['exception'][] = [
                    'type' => get_class($exception),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                ];
            } while ($exception = $exception->getPrevious());
        }

        return json_encode($error, JSON_PRETTY_PRINT);
    }
}
