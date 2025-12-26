<?php

namespace App\Exceptions;

use Exception;

class VirusScanFailedException extends Exception
{
    public string $userMessage;
    public string $reason;
    public array $meta;

    public function __construct(
        string $userMessage,
        string $reason = 'unknown',
        array $meta = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($reason, $code, $previous);
        $this->userMessage = $userMessage;
        $this->reason = $reason;
        $this->meta = $meta;
    }
}
