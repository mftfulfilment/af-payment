<?php

namespace App\Exceptions;

use Error;
use Exception;

class AfricasTalkingGatewayException extends Exception
{
    public function render($request)
    {
        return response()->json(["error" => true, "message" => $this->getMessage()]);
    }
}
