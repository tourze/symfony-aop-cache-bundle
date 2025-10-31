# AopCacheBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-aop-cache-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-cache-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/symfony-aop-cache-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-cache-bundle)
[![License](https://img.shields.io/github/license/tourze/symfony-aop-cache-bundle?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/monorepo/test.yml?style=flat-square)](https://github.com/tourze/monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/monorepo?style=flat-square)](https://codecov.io/gh/tourze/monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-aop-cache-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-cache-bundle)

AopCacheBundle is a Symfony bundle that provides advanced, annotation-driven 
caching capabilities using AOP (Aspect-Oriented Programming). It enables 
developers to add cache logic to methods declaratively, supporting cache tags, 
TTL, custom keys, and forced cache refresh.

## Features

- Annotation-based declarative cache (`#[Cacheble]`, `#[CachePut]`)
- Custom cache key with Twig syntax
- Cache tags for batch management and cleaning
- TTL (expiration) control
- Forced cache refresh support
- Extensible aspects and cache logic

## Dependencies

This package requires the following dependencies:

- PHP >= 8.1
- Symfony Framework Bundle >= 6.4
- Symfony Cache Component >= 6.4
- Redis PHP extension
- Twig template engine
- AOP Bundle for aspect-oriented programming support

## Installation

**Install via Composer:**

```bash
composer require tourze/symfony-aop-cache-bundle
```

**Enable the Bundle:**

```php
// config/bundles.php
return [
    // ...
    Tourze\Symfony\AopCacheBundle\AopCacheBundle::class => ['all' => true],
    // ...
];
```

## Configuration

The bundle automatically integrates with Symfony's cache configuration. 
Ensure your cache is properly configured:

```yaml
# config/packages/cache.yaml
framework:
    cache:
        app: cache.adapter.redis
        pools:
            cache.app:
                adapter: cache.adapter.redis
                default_lifetime: 3600
```

## Quick Start

### Basic Usage

1. Add `#[Cacheble]` annotation to your method:

```php
use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;

class UserService
{
    #[Cacheble(ttl: 3600, tags: ["user"])]
    public function getUserProfile(int $userId): array
    {
        // business logic
    }
}
```

2. Use custom cache key and tags:

```php
#[Cacheble(key: "profile_{{ userId }}", tags: ["profile", "user_{{ userId }}"])]
public function getUserProfile(int $userId): array
{
    // ...
}
```

3. Force cache refresh:

```php
use Tourze\Symfony\AopCacheBundle\Attribute\CachePut;

#[CachePut(key: "profile_{{ userId }}")]
public function updateProfile(int $userId, array $data): array
{
    // ...
}
```

## Cache Key Templates

Cache key template supports:
- Access parameters: `{{ paramName }}`
- Access join point info: `{{ joinPoint.method }}`, `{{ joinPoint.class }}`
- Supports all Twig syntax

## Advanced Usage

## Cache Management Commands

The bundle provides a command for batch cache cleaning by tags:

```bash
# Clear Redis cache by tags (runs daily at 5:10 AM by default)
php bin/console cache:redis-clear-tags
```

## Extending Cache Logic

You can extend the bundle's functionality by:

1. **Custom Cache Aspects**: Extend `CachebleAspect` or `CachePutAspect`
2. **Custom Cache Traits**: Use `CacheTrait` for reusable cache logic
3. **Custom Attributes**: Implement `CacheAttributeInterface`

## Best Practices

- **Supported Return Types**: Cache simple types (strings, numbers, arrays) 
  or serializable objects
- **Avoid Caching**: Resource types, callbacks, or complex entity objects
- **Performance**: Use cache tags for efficient batch invalidation
- **Custom Logic**: Extend `CacheTrait`, `CachebleAspect`, or `CachePutAspect` 
  for custom behavior

## Contributing

1. Fork the repository
2. Create a feature branch
3. Ensure all tests pass: `phpunit`
4. Follow PSR-12 coding standards
5. Submit a pull request

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for detailed contribution guidelines.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
