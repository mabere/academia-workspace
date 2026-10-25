<?php

namespace App\Exceptions;

use RuntimeException;

class AiUnavailableException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct('AI feedback is unavailable: '.$reason);
    }
}
