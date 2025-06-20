<?php

// File: app/Core/Security/CsrfMiddleware.php
namespace App\Core\Security;

class CsrfMiddleware
{
    /**
     * Handle CSRF verification for the request
     */
    public function handle(): void
    {
        try {
            CsrfProtection::verifyRequest();
        } catch (CsrfException $e) {
            $this->handleCsrfFailure($e);
        }
    }
    
    /**
     * Handle CSRF verification failure
     */
    private function handleCsrfFailure(CsrfException $e): void
    {
        // Log the attempt
        error_log(sprintf(
            'CSRF verification failed: %s from IP %s for URL %s',
            $e->getMessage(),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? 'unknown'
        ));
        
        // Return appropriate response
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'error' => 'CSRF token validation failed',
                'message' => 'Your session has expired. Please refresh the page and try again.'
            ]);
        } else {
            http_response_code(403);
            $_SESSION['csrf_error'] = 'Your session has expired. Please refresh the page and try again.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        }
        
        exit;
    }
    
    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}