<?php

declare(strict_types=1);

namespace MakeCommerce;

use Exception;

class MCException extends Exception
{
    /**
     * @param $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
