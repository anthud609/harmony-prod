<?php

// File: app/Core/Container/Providers/LoggingServiceProvider.php

namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggingServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            LoggerInterface::class => \DI\factory([$this, 'createLogger']),
            Logger::class => \DI\factory([$this, 'createLogger']),
            'logger' => \DI\get(LoggerInterface::class),
        ];
    }

    public function createLogger(): Logger
    {
        $logger = new Logger('harmony');

        // Get environment variables
        $appEnv = $_ENV['APP_ENV'] ?? 'production';
        $appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Define log directory
        $logDir = dirname(__DIR__, 4) . '/storage/logs';

        // Create logs directory if it doesn't exist
        if (! is_dir($logDir)) {
            mkdir($logDir, 0o755, true);
        }

        // Custom format for better readability
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat, true, true);

        // 1. Error Log (error level and above - always active)
        $errorHandler = new StreamHandler($logDir . '/error.log', Logger::ERROR);
        $errorHandler->setFormatter($formatter);
        $logger->pushHandler($errorHandler);

        // 2. App Log (all levels except debug, only if NOT production)
        if ($appEnv !== 'production') {
            // Create a handler that logs everything
            $appHandler = new StreamHandler($logDir . '/app.log', Logger::DEBUG);
            $appHandler->setFormatter($formatter);

            // Filter out debug messages
            $filteredAppHandler = new FilterHandler(
                $appHandler,
                Logger::INFO, // Minimum level (INFO and above)
                Logger::EMERGENCY // Maximum level
            );

            $logger->pushHandler($filteredAppHandler);
        }

        // 3. Debug Log (only debug messages, only if APP_DEBUG is true)
        if ($appDebug) {
            // Create a handler that only logs DEBUG level
            $debugHandler = new StreamHandler($logDir . '/debug.log', Logger::DEBUG);
            $debugHandler->setFormatter($formatter);

            // Filter to only debug messages
            $filteredDebugHandler = new FilterHandler(
                $debugHandler,
                Logger::DEBUG,  // Minimum level
                Logger::DEBUG   // Maximum level (only DEBUG)
            );

            $logger->pushHandler($filteredDebugHandler);
        }

        // Add extra information to all log entries
        $logger->pushProcessor(function ($record) {
            // Add request information if available
            if (isset($_SERVER['REQUEST_URI'])) {
                $record['extra']['uri'] = $_SERVER['REQUEST_URI'];
                $record['extra']['method'] = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
                $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'localhost';
            }

            // Add memory usage
            $record['extra']['memory'] = round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB';

            return $record;
        });

        return $logger;
    }
}
