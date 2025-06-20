<?php
// File: app/Core/Helpers/logger.php
// Global helper function for logging

use App\Core\Container\ContainerFactory;
use Psr\Log\LoggerInterface;

if (!function_exists('logger')) {
    /**
     * Get the logger instance or log a message
     * 
     * @param string|null $message
     * @param array $context
     * @param string $level
     * @return LoggerInterface|null
     */
    function logger(?string $message = null, array $context = [], string $level = 'info'): ?LoggerInterface
    {
        $container = ContainerFactory::getInstance();
        $logger = $container->get(LoggerInterface::class);
        
        if ($message === null) {
            return $logger;
        }
        
        // Log the message at the specified level
        switch ($level) {
            case 'debug':
                $logger->debug($message, $context);
                break;
            case 'info':
                $logger->info($message, $context);
                break;
            case 'notice':
                $logger->notice($message, $context);
                break;
            case 'warning':
            case 'warn':
                $logger->warning($message, $context);
                break;
            case 'error':
                $logger->error($message, $context);
                break;
            case 'critical':
                $logger->critical($message, $context);
                break;
            case 'alert':
                $logger->alert($message, $context);
                break;
            case 'emergency':
                $logger->emergency($message, $context);
                break;
            default:
                $logger->info($message, $context);
        }
        
        return null;
    }
}

if (!function_exists('log_debug')) {
    function log_debug(string $message, array $context = []): void
    {
        logger($message, $context, 'debug');
    }
}

if (!function_exists('log_info')) {
    function log_info(string $message, array $context = []): void
    {
        logger($message, $context, 'info');
    }
}

if (!function_exists('log_warning')) {
    function log_warning(string $message, array $context = []): void
    {
        logger($message, $context, 'warning');
    }
}

if (!function_exists('log_error')) {
    function log_error(string $message, array $context = []): void
    {
        logger($message, $context, 'error');
    }
}

if (!function_exists('log_critical')) {
    function log_critical(string $message, array $context = []): void
    {
        logger($message, $context, 'critical');
    }
}