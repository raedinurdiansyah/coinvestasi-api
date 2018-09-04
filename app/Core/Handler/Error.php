<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Error as SlimError;

/**
 * Class Error
 * @package ArrayIterator\Coinvestasi\Core\Handler
 */
class Error extends SlimError
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
     * Render JSON error
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Exception $exception)
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

        return json_encode($error, $this->option);
    }
}
