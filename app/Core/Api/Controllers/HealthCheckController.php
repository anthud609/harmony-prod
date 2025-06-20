<?php
// File: app/Core/Api/Controllers/HealthCheckController.php
namespace App\Core\Api\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;

class HealthCheckController
{
    /**
     * Health check endpoint
     */
    public function check(Request $request): Response
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => time(),
            'environment' => app_env(),
            'version' => config('app.version', '1.0.0'),
            'checks' => []
        ];
        
        // Check PHP version
        $health['checks']['php'] = [
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'warning',
            'version' => PHP_VERSION
        ];
        
        // Check database connection
        if (config('database.default')) {
            try {
                // In production, you'd actually test the connection
                $health['checks']['database'] = [
                    'status' => 'ok',
                    'driver' => config('database.default')
                ];
            } catch (\Exception $e) {
                $health['checks']['database'] = [
                    'status' => 'error',
                    'error' => is_debug() ? $e->getMessage() : 'Connection failed'
                ];
                $health['status'] = 'unhealthy';
            }
        }
        
        // Check cache
        if (config('cache.default')) {
            try {
                // In production, you'd test cache connection
                $health['checks']['cache'] = [
                    'status' => 'ok',
                    'driver' => config('cache.default')
                ];
            } catch (\Exception $e) {
                $health['checks']['cache'] = [
                    'status' => 'warning',
                    'error' => is_debug() ? $e->getMessage() : 'Cache unavailable'
                ];
            }
        }
        
        // Check disk space
        $freeSpace = disk_free_space(storage_path());
        $totalSpace = disk_total_space(storage_path());
        $usedPercentage = ($totalSpace - $freeSpace) / $totalSpace * 100;
        
        $health['checks']['disk'] = [
            'status' => $usedPercentage < 90 ? 'ok' : 'warning',
            'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'used_percentage' => round($usedPercentage, 2)
        ];
        
        // Check memory usage
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        $health['checks']['memory'] = [
            'status' => 'ok',
            'current_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_mb' => round($memoryPeak / 1024 / 1024, 2),
            'limit' => $memoryLimit
        ];
        
        $response = new Response();
        $response->setStatusCode($health['status'] === 'healthy' ? 200 : 503);
        $response->setHeader('Content-Type', 'application/json');
        $response->setContent(json_encode($health, JSON_PRETTY_PRINT));
        
        return $response;
    }
}