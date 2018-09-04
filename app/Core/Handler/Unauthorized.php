<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\AbstractHandler;
use Slim\Http\RequestBody;
use UnexpectedValueException;

/**
 * Class PermissionDenied
 * @package Slim\Handlers
 */
class Unauthorized extends AbstractHandler
{
    protected $option = JSON_PRETTY_PRINT;

    /**
     * Invoke Forbidden handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object
     * @param  ResponseInterface      $response The most recent Response object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $contentType = 'text/plain';
            $output = $this->renderPlainForbiddenOutput();
        } else {
            $contentType = $this->determineContentType($request);
            switch ($contentType) {
                case 'application/json':
                    $output = $this->renderJsonForbiddenOutput();
                    break;

                case 'text/xml':
                case 'application/xml':
                    $output = $this->renderXmlForbiddenOutput();
                    break;

                case 'text/html':
                    $output = $this->renderHtmlForbiddenOutput($request);
                    break;

                default:
                    throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
            }
        }

        $body = new RequestBody();
        $body->write($output);

        return $response->withStatus(401)
                        ->withHeader('Content-Type', $contentType)
                        ->withBody($body);
    }


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
     * @return string
     */
    protected function renderPlainForbiddenOutput()
    {
        return 'Forbidden';
    }

    /**
     * @return string
     */
    protected function renderJsonForbiddenOutput()
    {
        return json_encode([
            'error' => [
                'message' => '403 Forbidden'
            ]
        ], $this->option);
    }

    /**
     * @return string
     */
    protected function renderXmlForbiddenOutput()
    {
        return '<root><message>Forbidden</message></root>';
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function renderHtmlForbiddenOutput(ServerRequestInterface $request)
    {
        return <<<END
<html>
    <head>
        <title>Forbidden</title>
        <style>
            body{
                margin:0;
                padding:30px;
                font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;
            }
            h1{
                margin:0;
                font-size:48px;
                font-weight:normal;
                line-height:48px;
            }
            strong{
                display:inline-block;
                width:65px;
            }
        </style>
    </head>
    <body>
        <h1>Forbidden</h1>
        <p>
            You have not enough permission to access this page.
        </p>
    </body>
</html>
END;
    }
}
