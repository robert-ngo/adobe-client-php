<?php

declare(strict_types=1);

namespace Adobe\Client\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

final class ApiException extends \RuntimeException
{
    private ?ResponseInterface $response;

    public function __construct(string $message, ?ResponseInterface $response = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}


