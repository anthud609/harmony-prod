<?php


// File: app/Core/Http/Middleware/MiddlewareInterface.php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}
