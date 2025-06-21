<?php

// File: app/Core/Config/ConfigManager.php

namespace App\Core\Config;

use Dotenv\Dotenv;

class ConfigManager
{
    private static ?ConfigManager $instance = null;
    private array $config = [];
    private array $cache = [];
    private bool $loaded = false;

    private function __construct()
    {
        // Don't auto-load in constructor to prevent recursion
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize configuration (call this explicitly)
     */
    public function initialize(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loadEnvironment();
        $this->loadConfigurations();
        $this->loaded = true;
    }

    /**
     * Load environment variables
     */
    private function loadEnvironment(): void
    {
        $rootPath = dirname(__DIR__, 3);

        // Only load if .env exists
        if (file_exists($rootPath . '/.env')) {
            try {
                $dotenv = Dotenv::createImmutable($rootPath);
                $dotenv->safeLoad(); // Use safeLoad to not fail if file missing
            } catch (\Exception $e) {
                // Log error but don't fail
                error_log('Failed to load .env: ' . $e->getMessage());
            }
        }
    }

    /**
     * Load all configuration files
     */
    private function loadConfigurations(): void
    {
        $configPath = dirname(__DIR__, 3) . '/config';

        // If config directory doesn't exist, use defaults
        if (! is_dir($configPath)) {
            $this->loadDefaultConfigurations();

            return;
        }

        // Load each config file
        $configFiles = glob($configPath . '/*.php');
        foreach ($configFiles as $file) {
            $name = basename($file, '.php');

            try {
                $this->config[$name] = require $file;
            } catch (\Exception $e) {
                error_log("Failed to load config file $file: " . $e->getMessage());
                // Use defaults for this config
                $this->config[$name] = $this->getDefaultConfig($name);
            }
        }
    }

    /**
     * Get default config by name
     */
    private function getDefaultConfig(string $name): array
    {
        switch ($name) {
            case 'app':
                return $this->getAppConfig();
            case 'database':
                return $this->getDatabaseConfig();
            case 'cache':
                return $this->getCacheConfig();
            case 'session':
                return $this->getSessionConfig();
            default:
                return [];
        }
    }

    /**
     * Load default configurations
     */
    private function loadDefaultConfigurations(): void
    {
        $this->config = [
            'app' => $this->getAppConfig(),
            'database' => $this->getDatabaseConfig(),
            'cache' => $this->getCacheConfig(),
            'session' => $this->getSessionConfig(),
            'logging' => $this->getLoggingConfig(),
            'security' => $this->getSecurityConfig(),
            'api' => $this->getApiConfig(),
            'search' => $this->getSearchConfig(),
            'features' => $this->getFeatureConfig(),
        ];
    }

    /**
     * Get configuration value using dot notation
     */
    public function get(string $key, $default = null)
    {
        // Ensure config is loaded
        if (! $this->loaded) {
            $this->initialize();
        }

        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // Parse dot notation
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (! isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        // Cache the result
        $this->cache[$key] = $value;

        return $value;
    }

    /**
     * Set configuration value
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (! isset($config[$k]) || ! is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }

        // Clear cache for this key
        unset($this->cache[$key]);
    }

    /**
     * Check if configuration exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all configuration
     */
    public function all(): array
    {
        if (! $this->loaded) {
            $this->initialize();
        }

        return $this->config;
    }

    /**
     * Get environment variable with fallback
     */
    public function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        // Convert string booleans
        if (is_string($value)) {
            $valueLower = strtolower($value);
            if ($valueLower === 'true' || $valueLower === '(true)') {
                return true;
            }
            if ($valueLower === 'false' || $valueLower === '(false)') {
                return false;
            }
            if ($valueLower === 'null' || $valueLower === '(null)') {
                return null;
            }
        }

        return $value;
    }

    // Default configuration methods

    private function getAppConfig(): array
    {
        return [
            'name' => $this->env('APP_NAME', 'Harmony HRMS'),
            'env' => $this->env('APP_ENV', 'production'),
            'debug' => $this->env('APP_DEBUG', false),
            'url' => $this->env('APP_URL', 'http://localhost'),
            'timezone' => $this->env('APP_TIMEZONE', 'UTC'),
            'locale' => $this->env('APP_LOCALE', 'en'),
            'key' => $this->env('APP_KEY', ''),
            'cipher' => 'AES-256-CBC',
        ];
    }

    private function getDatabaseConfig(): array
    {
        return [
            'default' => $this->env('DB_CONNECTION', 'mysql'),
            'connections' => [
                'mysql' => [
                    'driver' => 'mysql',
                    'host' => $this->env('DB_HOST', '127.0.0.1'),
                    'port' => $this->env('DB_PORT', '3306'),
                    'database' => $this->env('DB_DATABASE', 'harmony_hrms'),
                    'username' => $this->env('DB_USERNAME', 'root'),
                    'password' => $this->env('DB_PASSWORD', ''),
                    'charset' => $this->env('DB_CHARSET', 'utf8mb4'),
                    'collation' => $this->env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ],
            ],
        ];
    }

    private function getCacheConfig(): array
    {
        return [
            'default' => $this->env('CACHE_DRIVER', 'file'),
            'stores' => [
                'file' => [
                    'driver' => 'file',
                    'path' => dirname(__DIR__, 3) . '/storage/cache',
                ],
                'redis' => [
                    'driver' => 'redis',
                    'connection' => 'cache',
                ],
            ],
            'prefix' => $this->env('CACHE_PREFIX', 'harmony_cache'),
            'ttl' => (int) $this->env('CACHE_DEFAULT_TTL', 3600),
        ];
    }

    private function getSessionConfig(): array
    {
        return [
            'driver' => $this->env('SESSION_DRIVER', 'file'),
            'lifetime' => (int) $this->env('SESSION_LIFETIME', 360),
            'warning_time' => (int) $this->env('SESSION_WARNING_TIME', 60),
            'expire_on_close' => false,
            'encrypt' => false,
            'files' => dirname(__DIR__, 3) . '/storage/sessions',
            'connection' => null,
            'table' => 'sessions',
            'store' => null,
            'lottery' => [2, 100],
            'cookie' => $this->env('SESSION_COOKIE_NAME', 'HARMONY_SESSID'),
            'path' => '/',
            'domain' => null,
            'secure' => $this->env('SESSION_COOKIE_SECURE', false),
            'http_only' => $this->env('SESSION_COOKIE_HTTPONLY', true),
            'same_site' => $this->env('SESSION_COOKIE_SAMESITE', 'lax'),
        ];
    }

    private function getLoggingConfig(): array
    {
        return [
            'default' => $this->env('LOG_CHANNEL', 'daily'),
            'channels' => [
                'daily' => [
                    'driver' => 'daily',
                    'path' => dirname(__DIR__, 3) . '/storage/logs/harmony.log',
                    'level' => $this->env('LOG_LEVEL', 'debug'),
                    'days' => (int) $this->env('LOG_MAX_FILES', 14),
                ],
            ],
        ];
    }

    private function getSecurityConfig(): array
    {
        return [
            'csrf' => [
                'token_name' => 'csrf_token',
                'header_name' => 'X-CSRF-Token',
                'token_lifetime' => (int) $this->env('CSRF_TOKEN_LIFETIME', 3600),
                'max_tokens' => 5,
            ],
        ];
    }

    private function getApiConfig(): array
    {
        return [
            'rate_limit' => (int) $this->env('API_RATE_LIMIT', 60),
            'rate_limit_window' => (int) $this->env('API_RATE_LIMIT_WINDOW', 60),
            'version' => $this->env('API_VERSION', 'v1'),
            'debug' => $this->env('API_DEBUG_ENABLED', false),
        ];
    }

    private function getSearchConfig(): array
    {
        return [
            'driver' => $this->env('SEARCH_DRIVER', 'database'),
            'min_length' => (int) $this->env('SEARCH_MIN_LENGTH', 2),
            'max_length' => (int) $this->env('SEARCH_MAX_LENGTH', 100),
            'timeout' => (int) $this->env('SEARCH_TIMEOUT', 5),
            'cache_ttl' => (int) $this->env('SEARCH_CACHE_TTL', 300),
        ];
    }

    private function getFeatureConfig(): array
    {
        return [
            'employee_self_service' => $this->env('FEATURE_EMPLOYEE_SELF_SERVICE', true),
            'advanced_reporting' => $this->env('FEATURE_ADVANCED_REPORTING', true),
            'api_access' => $this->env('FEATURE_API_ACCESS', true),
            'two_factor_auth' => $this->env('FEATURE_TWO_FACTOR_AUTH', false),
            'audit_logging' => $this->env('FEATURE_AUDIT_LOGGING', true),
            'real_time_notifications' => $this->env('FEATURE_REAL_TIME_NOTIFICATIONS', false),
        ];
    }
}
