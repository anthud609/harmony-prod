<?php

// File: app/Core/Layout/CachedComponentRenderer.php

namespace App\Core\Layout;

use App\Core\Traits\LoggerTrait;

/**
 * Component renderer with caching capabilities
 */
class CachedComponentRenderer extends ComponentRenderer
{
    use LoggerTrait;

    private array $cacheConfig = [
        // Component => TTL in seconds (0 = no cache)
        'sidebar' => 3600,      // 1 hour
        'header' => 1800,       // 30 minutes (user menu might change)
        'scripts' => 86400,     // 24 hours
        'globalScripts' => 86400, // 24 hours
        'pageHeader' => 0,      // Don't cache (dynamic content)
        'messages' => 0,        // Don't cache (user-specific)
        'notifications' => 0,   // Don't cache (user-specific)
        'userMenu' => 0,        // Don't cache (user-specific)
        'commandPalette' => 300, // 5 minutes
    ];

    private ?object $cacheDriver = null;
    private bool $cacheEnabled = true;
    private string $cachePrefix = 'harmony:component:';

    public function __construct(
        ComponentRegistry $registry,
        ?object $cacheDriver = null,
        bool $cacheEnabled = true
    ) {
        parent::__construct($registry);
        $this->cacheDriver = $cacheDriver;
        $this->cacheEnabled = $cacheEnabled && $cacheDriver !== null;
    }

    /**
     * Render a component with caching
     */
    public function render(string $componentName, array $data = []): void
    {
        // Check if caching is enabled and component is cacheable
        if (! $this->shouldCache($componentName)) {
            parent::render($componentName, $data);

            return;
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($componentName, $data);

        // Try to get from cache
        $cached = $this->getFromCache($cacheKey);
        if ($cached !== null) {
            $this->logDebug("Component rendered from cache", [
                'component' => $componentName,
                'cacheKey' => $cacheKey,
            ]);
            echo $cached;

            return;
        }

        // Render and cache
        ob_start();
        parent::render($componentName, $data);
        $output = ob_get_clean();

        // Store in cache
        $ttl = $this->cacheConfig[$componentName] ?? 0;
        if ($ttl > 0) {
            $this->storeInCache($cacheKey, $output, $ttl);
            $this->logDebug("Component cached", [
                'component' => $componentName,
                'ttl' => $ttl,
                'cacheKey' => $cacheKey,
            ]);
        }

        echo $output;
    }

    /**
     * Check if component should be cached
     */
    private function shouldCache(string $componentName): bool
    {
        if (! $this->cacheEnabled) {
            return false;
        }

        // Check if component has cache configuration
        $ttl = $this->cacheConfig[$componentName] ?? 0;

        return $ttl > 0;
    }

    /**
     * Generate cache key for component
     */
    private function generateCacheKey(string $componentName, array $data): string
    {
        // Extract cache-relevant data
        $cacheData = $this->extractCacheableData($componentName, $data);

        // Create a deterministic key
        $key = $this->cachePrefix . $componentName . ':' . md5(serialize($cacheData));

        return $key;
    }

    /**
     * Extract only cacheable data (exclude user-specific data for some components)
     */
    private function extractCacheableData(string $componentName, array $data): array
    {
        switch ($componentName) {
            case 'sidebar':
                // Sidebar might vary by user role
                return [
                    'role' => $data['user']['role'] ?? 'user',
                    'theme' => $data['user']['preferredTheme'] ?? 'light',
                ];

            case 'header':
                // Header varies by theme only (user menu is separate)
                return [
                    'theme' => $data['user']['preferredTheme'] ?? 'light',
                ];

            case 'scripts':
            case 'globalScripts':
                // Scripts might vary by environment
                return [
                    'env' => $_ENV['APP_ENV'] ?? 'production',
                ];

            default:
                return [];
        }
    }

    /**
     * Get from cache
     */
    private function getFromCache(string $key): ?string
    {
        if (! $this->cacheDriver) {
            return null;
        }

        try {
            // Redis implementation
            if (method_exists($this->cacheDriver, 'get')) {
                return $this->cacheDriver->get($key) ?: null;
            }

            // File cache implementation
            if (method_exists($this->cacheDriver, 'fetch')) {
                $cached = $this->cacheDriver->fetch($key);

                return $cached !== false ? $cached : null;
            }
        } catch (\Exception $e) {
            $this->logError("Cache read failed", [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Store in cache
     */
    private function storeInCache(string $key, string $value, int $ttl): void
    {
        if (! $this->cacheDriver) {
            return;
        }

        try {
            // Redis implementation
            if (method_exists($this->cacheDriver, 'setex')) {
                $this->cacheDriver->setex($key, $ttl, $value);
            }
            // Alternative Redis implementation
            elseif (method_exists($this->cacheDriver, 'set')) {
                $this->cacheDriver->set($key, $value, $ttl);
            }
            // File cache implementation
            elseif (method_exists($this->cacheDriver, 'save')) {
                $this->cacheDriver->save($key, $value, $ttl);
            }
        } catch (\Exception $e) {
            $this->logError("Cache write failed", [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear cache for specific component or all components
     */
    public function clearCache(?string $componentName = null): void
    {
        if (! $this->cacheDriver) {
            return;
        }

        try {
            if ($componentName) {
                // Clear specific component cache
                $pattern = $this->cachePrefix . $componentName . ':*';
                $this->clearByPattern($pattern);
                $this->logInfo("Component cache cleared", ['component' => $componentName]);
            } else {
                // Clear all component cache
                $pattern = $this->cachePrefix . '*';
                $this->clearByPattern($pattern);
                $this->logInfo("All component cache cleared");
            }
        } catch (\Exception $e) {
            $this->logError("Cache clear failed", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Clear cache by pattern
     */
    private function clearByPattern(string $pattern): void
    {
        // Redis implementation
        if (method_exists($this->cacheDriver, 'eval')) {
            // Use Lua script for atomic delete
            $lua = "return redis.call('del', unpack(redis.call('keys', ARGV[1])))";
            $this->cacheDriver->eval($lua, 0, $pattern);
        }
        // Simple implementation - delete by pattern
        elseif (method_exists($this->cacheDriver, 'deleteByPattern')) {
            $this->cacheDriver->deleteByPattern($pattern);
        }
    }

    /**
     * Set cache configuration for a component
     */
    public function setCacheTTL(string $componentName, int $ttl): void
    {
        $this->cacheConfig[$componentName] = $ttl;
    }

    /**
     * Disable cache for specific component
     */
    public function disableCache(string $componentName): void
    {
        $this->cacheConfig[$componentName] = 0;
    }
}
