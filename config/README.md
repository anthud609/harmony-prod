# Harmony HRMS Configuration System

## Overview

The Harmony HRMS application now uses a comprehensive environment-based configuration system that allows you to easily manage settings across different environments (local, staging, production).

## Setup

### 1. Environment File

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Then update the values according to your environment.

### 2. Environment-Specific Files

You can create environment-specific configuration files:

- `.env.local` - For local development
- `.env.staging` - For staging environment
- `.env.production` - For production environment

The system will automatically load the appropriate file based on the `APP_ENV` value.

## Configuration Structure

### Core Configuration Files

The application uses configuration files located in the `/config` directory:

- `app.php` - Application settings
- `database.php` - Database connections
- `cache.php` - Cache configuration
- `session.php` - Session settings
- `logging.php` - Logging configuration
- `security.php` - Security settings
- `api.php` - API configuration
- `features.php` - Feature flags

### Accessing Configuration

You can access configuration values using the `config()` helper function:

```php
// Get a value
$appName = config('app.name');

// Get with default
$timezone = config('app.timezone', 'UTC');

// Get nested values
$driver = config('database.connections.mysql.driver');

// Check if exists
if (config()->has('feature.two_factor_auth')) {
    // ...
}
```

### Environment Variables

Access environment variables using the `env()` helper:

```php
$debug = env('APP_DEBUG', false);
```

### Helper Functions

The system provides several helper functions:

- `app_name()` - Get application name
- `app_env()` - Get current environment
- `is_production()` - Check if in production
- `is_local()` - Check if in local environment
- `is_debug()` - Check if debug mode is enabled
- `feature($name)` - Check if a feature is enabled
- `storage_path($path)` - Get storage path
- `config_path($path)` - Get config path
- `base_path($path)` - Get base application path

## Key Configuration Options

### Application Settings

- `APP_NAME` - Application name
- `APP_ENV` - Environment (local, staging, production)
- `APP_DEBUG` - Debug mode (true/false)
- `APP_URL` - Application URL
- `APP_KEY` - Encryption key (32 characters)

### Database

- `DB_CONNECTION` - Database driver (mysql, pgsql, sqlite)
- `DB_HOST` - Database host
- `DB_PORT` - Database port
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password

### Session

- `SESSION_DRIVER` - Session storage driver
- `SESSION_LIFETIME` - Session lifetime in seconds
- `SESSION_WARNING_TIME` - Warning time before expiry
- `SESSION_COOKIE_NAME` - Cookie name
- `SESSION_COOKIE_SECURE` - HTTPS only cookies

### Cache

- `CACHE_DRIVER` - Cache driver (file, redis, memcached)
- `CACHE_PREFIX` - Cache key prefix
- `CACHE_DEFAULT_TTL` - Default cache TTL
- `VIEW_CACHE_ENABLED` - Enable view caching
- `QUERY_CACHE_ENABLED` - Enable query caching

### Security

- `CSRF_TOKEN_LIFETIME` - CSRF token lifetime
- `API_RATE_LIMIT` - API rate limit per minute
- `CORS_ALLOWED_ORIGINS` - CORS allowed origins

### Features

Feature flags allow you to enable/disable functionality:

- `FEATURE_EMPLOYEE_SELF_SERVICE`
- `FEATURE_ADVANCED_REPORTING`
- `FEATURE_API_ACCESS`
- `FEATURE_TWO_FACTOR_AUTH`
- `FEATURE_AUDIT_LOGGING`
- `FEATURE_REAL_TIME_NOTIFICATIONS`

## Environment-Specific Configuration

### Local Development

```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=localhost
CACHE_DRIVER=array
SESSION_DRIVER=file
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=true
DB_HOST=staging-db.example.com
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=prod-db.example.com
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_COOKIE_SECURE=true
```

## Maintenance Mode

Enable maintenance mode by setting:

```env
APP_MAINTENANCE=true
APP_MAINTENANCE_MESSAGE="We are currently performing scheduled maintenance."
APP_MAINTENANCE_ALLOWED_IPS=192.168.1.1,10.0.0.1
```

## Performance Optimization

### Caching Configuration

In production, you should cache the configuration for better performance:

```php
// Cache configuration (run during deployment)
php artisan config:cache

// Clear configuration cache
php artisan config:clear
```

### Environment Optimization

- Use Redis for cache and sessions in production
- Enable query and view caching
- Set appropriate cache TTL values
- Use CDN for static assets

## Security Best Practices

1. **Never commit `.env` files** - They contain sensitive information
2. **Generate strong APP_KEY** - Use a 32-character random string
3. **Use HTTPS in production** - Set `SESSION_COOKIE_SECURE=true`
4. **Restrict allowed IPs** - For maintenance mode and admin areas
5. **Enable CORS properly** - Don't use `*` in production
6. **Rotate keys regularly** - Update APP_KEY periodically

## Troubleshooting

### Configuration Not Loading

1. Check if `.env` file exists
2. Verify file permissions (should be readable)
3. Clear configuration cache
4. Check for syntax errors in `.env`

### Environment Variables Not Working

1. Ensure variable names are correct
2. Check for spaces around `=` in `.env`
3. Use quotes for values with spaces
4. Restart web server after changes

### Performance Issues

1. Enable configuration caching
2. Use appropriate cache drivers
3. Check cache hit rates
4. Monitor slow queries

## Adding New Configuration

1. Add to `.env.example`:
```env
MY_NEW_SETTING=default_value
```

2. Create/update config file:
```php
// config/myconfig.php
return [
    'setting' => env('MY_NEW_SETTING', 'default'),
];
```

3. Access in code:
```php
$value = config('myconfig.setting');
```

## Migration from Hard-coded Values

The application has been updated to use configuration instead of hard-coded values:

- Session lifetime: `config('session.lifetime')`
- Cache TTL: `config('cache.ttl')`
- API limits: `config('api.rate_limit')`
- Feature flags: `feature('feature_name')`

All components now read from configuration, making the application fully configurable per environment.