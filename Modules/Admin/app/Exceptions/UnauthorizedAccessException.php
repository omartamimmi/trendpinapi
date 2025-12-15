<?php

namespace Modules\Admin\app\Exceptions;

use Exception;

class UnauthorizedAccessException extends Exception
{
    protected $code = 403;
}
