<?php
// File: app/Core/Traits/LoggerTrait.php
namespace App\Core\Traits;

use Psr\Log\LoggerInterface;
use App\Core\Container\ContainerFactory;

trait LoggerTrait
{
    private ?LoggerInterface $logger = null;
    
    /**
     * Get the logger instance
     */
    protected function logger(): LoggerInterface
    {
        if ($this->logger === null) {
            $container = ContainerFactory::getInstance();
            $this->logger = $container->get(LoggerInterface::class);
        }
        
        return $this->logger;
    }
    
    /**
     * Log debug message
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->logger()->debug($message, $context);
    }
    
    /**
     * Log info message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->logger()->info($message, $context);
    }
    
    /**
     * Log notice message
     */
    protected function logNotice(string $message, array $context = []): void
    {
        $this->logger()->notice($message, $context);
    }
    
    /**
     * Log warning message
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->logger()->warning($message, $context);
    }
    
    /**
     * Log error message
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->logger()->error($message, $context);
    }
    
    /**
     * Log critical message
     */
    protected function logCritical(string $message, array $context = []): void
    {
        $this->logger()->critical($message, $context);
    }
    
    /**
     * Log alert message
     */
    protected function logAlert(string $message, array $context = []): void
    {
        $this->logger()->alert($message, $context);
    }
    
    /**
     * Log emergency message
     */
    protected function logEmergency(string $message, array $context = []): void
    {
        $this->logger()->emergency($message, $context);
    }
}