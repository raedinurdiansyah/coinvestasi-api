<?php
namespace ArrayIterator\Coinvestasi\Core\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\AbstractHandler;
use Slim\Http\Body;
use UnexpectedValueException;

/**
 * Class Expired
 * @package ArrayIterator\Coinvestasi\Core\Handler
 */
class Expired extends AbstractHandler
{
    protected $option = JSON_PRETTY_PRINT;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $contentType = 'text/plain';
            $output = $this->renderPlainNotFoundOutput();
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

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response->withStatus(410)
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
    protected function renderPlainNotFoundOutput()
    {
        return 'Expired';
    }

    /**
     * @return string
     */
    protected function renderJsonForbiddenOutput()
    {
        return json_encode([
            'error' => [
                'message' => '410 Gone - Expired access.'
            ]
        ], $this->option);
    }

    /**
     * @return string
     */
    protected function renderXmlForbiddenOutput()
    {
        return '<root><message>Expired</message></root>';
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
        <h1>Expired</h1>
        <p>
            You have requested expired page.
        </p>
    </body>
</html>
END;
    }
}
