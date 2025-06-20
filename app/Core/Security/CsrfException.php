<?php

// File: app/Core/Security/CsrfException.php

namespace App\Core\Security;

use Exception;

class CsrfException extends Exception
{
    protected $code = 403;
}
