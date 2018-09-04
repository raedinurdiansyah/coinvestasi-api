<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2018, Pentagonal Development
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core\Exception;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpException
 * @package ArrayIterator\Coinvestasi\Core\Exception
 */
class HttpException extends RequestException
{
    /**
     * @var RequestInterface|null $request
     */
    protected $request;

    /**
     * @var ResponseInterface|null $response
     */
    protected $response;

    /**
     * HttpException constructor.
     *
     * @param string $message
     * @param RequestInterface $request
     * @param null $response
     * @param \Exception|null $previous
     * @param array $handlerContext
     */
    public function __construct(
        $message,
        $request,
        $response = null,
        $previous = null,
        $handlerContext = null
    ) {
        $code = is_int($request) ? $request : 0;
        $request = $request instanceof RequestInterface ? $request : null;
        $exception = $response instanceof \Exception ? $response : null;
        $exception = $exception === null && $previous instanceof \Exception
            ? $previous
            : null;
        $context = is_array($previous) ? $previous : null;
        if ($context === null) {
            $context = is_array($handlerContext) ? $handlerContext : null;
        }
        if (!is_array($context)) {
            $context = [];
        }
        $exception = $exception === null && $handlerContext instanceof \Exception
            ? $handlerContext
            : null;
        $response = $response instanceof ResponseInterface
            ? $response
            : null;
        $code = $code === 0 && $response ? $response->getStatusCode() : $code;
        $this->setCode($code);
        $this->message = $message;
        if ($request instanceof RequestException
            && ($response === null || $response instanceof ResponseInterface)
        ) {
            parent::__construct($message, $request, $response, $exception, $context);
        }
    }

    /**
     * @param int $code
     */
    public function setCode(int $code)
    {
        $this->code = $code;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file)
    {
        $this->file = $file;
    }

    /**
     * @param int $line
     */
    public function setLine(int $line)
    {
        $this->line = $line;
    }

    /**
     * Get the request that caused the exception
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the associated response
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
