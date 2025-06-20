<?php
// File: app/Core/Container/Providers/SecurityServiceProvider.php
namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use App\Core\Security\CsrfProtection;
use App\Core\Security\SessionManager;
use App\Core\Security\CsrfMiddleware;
use App\Core\Security\ContentSecurityPolicy;
use App\Core\Security\XssProtection;
use DI\Factory\RequestedEntry;

class SecurityServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            // Session Manager as singleton
            SessionManager::class => \DI\create()
                ->constructor(),
                
            // CSRF Protection as singleton 
            CsrfProtection::class => \DI\create()
                ->constructor(),
                
            // CSRF Middleware
            CsrfMiddleware::class => \DI\autowire(),
            
            // Content Security Policy
            ContentSecurityPolicy::class => \DI\autowire(),
            
            // XSS Protection (static class, but we can wrap it if needed)
            XssProtection::class => \DI\create()
                ->constructor(),
            
            // Convenience factories
            'session' => \DI\get(SessionManager::class),
            'csrf' => \DI\get(CsrfProtection::class),
            'csp' => \DI\get(ContentSecurityPolicy::class),
        ];
    }
}