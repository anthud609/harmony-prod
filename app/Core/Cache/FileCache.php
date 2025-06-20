<?php
// File: app/Core/Cache/FileCache.php
namespace App\Core\Cache;

/**
 * Simple file-based cache implementation
 */
class FileCache
{
    private string $cacheDir;
    
    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? dirname(__DIR__, 3) . '/storage/cache/components';
        
        // Ensure cache directory exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get value from cache
     */
    public function get(string $key): ?string
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        $data = unserialize($content);
        
        // Check expiration
        if ($data['expires'] < time()) {
            unlink($filename);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Set value in cache
     */
    public function set(string $key, string $value, int $ttl = 3600): bool
    {
        $filename = $this->getFilename($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($filename, serialize($data)) !== false;
    }
    
    /**
     * Fetch from cache (alias for get)
     */
    public function fetch(string $key)
    {
        $value = $this->get($key);
        return $value !== null ? $value : false;
    }
    
    /**
     * Save to cache (alias for set)
     */
    public function save(string $key, string $value, int $ttl = 3600): bool
    {
        return $this->set($key, $value, $ttl);
    }
    
    /**
     * Delete by pattern
     */
    public function deleteByPattern(string $pattern): int
    {
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '/^' . preg_quote($pattern, '/') . '$/';
        
        $deleted = 0;
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            $key = $this->getKeyFromFilename($file);
            if (preg_match($pattern, $key)) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Clear all cache
     */
    public function clear(): void
    {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * Garbage collection - remove expired entries
     */
    public function gc(): int
    {
        $cleaned = 0;
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = unserialize($content);
            
            if ($data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Get filename for cache key
     */
    private function getFilename(string $key): string
    {
        // Use MD5 to avoid filesystem issues with special characters
        $safeKey = md5($key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }
    
    /**
     * Get key from filename (for pattern matching)
     */
    private function getKeyFromFilename(string $filename): string
    {
        // In a real implementation, you'd store the original key in the file
        // For now, this is a simplified version
        return basename($filename, '.cache');
    }
}