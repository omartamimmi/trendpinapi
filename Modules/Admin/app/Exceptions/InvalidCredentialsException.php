<?php

namespace Modules\Admin\app\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $code = 401;
}
