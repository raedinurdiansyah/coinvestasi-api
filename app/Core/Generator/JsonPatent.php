<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core\Generator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\RequestBody;

/**
 * Class JsonPatent
 * @package ArrayIterator\Coinvestasi\Core\Generator
 */
class JsonPatent
{
    /**
     * @var string[]
     */
    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var ServerRequestInterface
     */
    public static $request;

    /**
     * @param int $code
     * @return string
     */
    public static function getErrorInternal(int $code = 500) : string
    {
        if (isset(self::$phrases[$code])) {
            $phrases = self::$phrases[$code];
            return "{$code} {$phrases}";
        }

        return "500 Internal Server Error";
    }

    /**
     * @param ResponseInterface $response
     * @param array $data
     * @return ResponseInterface
     */
    protected static function serveResponse(
        ResponseInterface $response,
        array $data
    ) : ResponseInterface {
        $params = !isset(self::$request) || !self::$request instanceof ServerRequestInterface
            ? $_GET
            : self::$request->getQueryParams();
        $jsonOption = JSON_PRETTY_PRINT;
        if (!empty($params['compress']) && is_string($params['compress'])
            && in_array($params['compress'], ['true', '1', 'yes'])
        ) {
            $jsonOption = 0;
        }

        $jsonOption |= JSON_UNESCAPED_SLASHES;
        $body = $response->getBody();
        $body = ! $body->getSize() ? $response->getBody() : new RequestBody();
        $body->write(json_encode($data, $jsonOption));
        return $response->withHeader(
            'Content-Type',
            'application/json;charset=utf-8'
        );
    }

    /**
     * @param ResponseInterface $response
     * @param mixed $data
     * @param int|null $statusCode
     * @return ResponseInterface
     */
    public static function successCode(
        ResponseInterface $response,
        $data,
        int $statusCode = 200
    ) : ResponseInterface {
        if ($statusCode) {
            $response = $response->withStatus($statusCode);
        }
        return self::serveResponse(
            $response,
            [
                //'success' => true,
                'data' => $data
            ]
        );
    }

    /**
     * @param ResponseInterface $response
     * @param null $message
     * @param int $statusCode
     * @return ResponseInterface
     */
    public static function errorCode(
        ResponseInterface $response,
        $message = null,
        int $statusCode = 500
    ) : ResponseInterface {
        $response = $response->withStatus($statusCode);
        $message = $message?:self::getErrorInternal($statusCode);
        $data = [
            'error' => [
                'message' => $message
            ]
        ];

        if (is_array($message) && !empty($message['message'])) {
            $data['error'] = $message;
        }

        return self::serveResponse(
            $response,
            $data
        );
    }

    /**
     * @param ResponseInterface $response
     * @param mixed $data
     * @return ResponseInterface
     */
    public static function success(
        ResponseInterface $response,
        $data
    ) : ResponseInterface {
        return self::successCode($response, $data, 200);
    }

    /**
     * @param ResponseInterface $response
     * @param mixed $message
     * @return ResponseInterface
     */
    public static function error(
        ResponseInterface $response,
        $message = null
    ) : ResponseInterface {
        return self::errorCode($response, $message, 500);
    }

    /**
     * @param string $message
     * @param int $code
     * @param int $line
     * @param string $file
     * @param array $trace
     * @return array
     */
    public static function generateResultException(
        string $message = '',
        int $code = 0,
        int $line = 0,
        string $file = '',
        array $trace = []
    ) : array {
        return [
            'message' => $message,
            'code'    => $code,
            'line'    => $line,
            'file'    => preg_replace_callback(
                '/(.+)([^\/]+)$/',
                function ($m) {
                    $sub = preg_quote(realpath(__DIR__ .'/../../../'), '/');
                    return preg_replace("/^{$sub}/", 'ROOT_DIR', $m[1]).$m[2];
                },
                $file
            ),
            'trace' => $trace
        ];
    }

    /**
     * @param ResponseInterface $response
     * @param string $message
     * @param int $code
     * @param int $line
     * @param string $file
     * @param array $trace
     * @return ResponseInterface
     */
    public static function withManualResultException(
        ResponseInterface $response,
        string $message = '',
        int $code = 0,
        int $line = 0,
        string $file = '',
        array $trace = []
    ) : ResponseInterface {
        return self::errorCode($response, self::generateResultException(
            $message,
            $code,
            $line,
            $file,
            $trace
        ), 500);
    }

    /**
     * @param ResponseInterface $response
     * @param \Throwable $e
     * @return ResponseInterface
     */
    public static function exception(
        ResponseInterface $response,
        \Throwable $e
    ) : ResponseInterface {
        return self::errorCode($response, self::generateResultException(
            $e->getMessage(),
            $e->getCode(),
            $e->getLine(),
            $e->getFile(),
            $e->getTrace()
        ), 500);
    }

    /**
     * @param ResponseInterface $response
     * @param mixed $message
     * @return ResponseInterface
     */
    public static function notFound(
        ResponseInterface $response,
        $message = null
    ) : ResponseInterface {
        return self::errorCode($response, $message, 404);
    }

    /**
     * @param ResponseInterface $response
     * @param mixed $message
     * @return ResponseInterface
     */
    public static function preconditionFailed(
        ResponseInterface $response,
        $message = null
    ) : ResponseInterface {
        return self::errorCode($response, $message, 412);
    }
}
