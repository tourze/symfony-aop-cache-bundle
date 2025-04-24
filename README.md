# AopCacheBundle

AopCacheBundle is a Symfony bundle that provides advanced, annotation-driven caching capabilities using AOP (Aspect-Oriented Programming). It enables developers to add cache logic to methods declaratively, supporting cache tags, TTL, custom keys, and forced cache refresh.

![Packagist Version](https://img.shields.io/packagist/v/tourze/symfony-aop-cache-bundle)
![License](https://img.shields.io/github/license/tourze/symfony-aop-cache-bundle)

## Features

- Annotation-based declarative cache (`#[Cacheble]`, `#[CachePut]`)
- Custom cache key with Twig syntax
- Cache tags for batch management and cleaning
- TTL (expiration) control
- Forced cache refresh support
- Extensible aspects and cache logic

## Installation

- Requires PHP >= 8.1
- Requires Symfony >= 6.4
- Requires Redis extension

```shell
composer require tourze/symfony-aop-cache-bundle
```

## Quick Start

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

4. Cache key template:
   - Access parameters: `{{ paramName }}`
   - Access join point info: `{{ joinPoint.method }}`, `{{ joinPoint.class }}`
   - Supports all Twig syntax

## Documentation

- Support batch cache cleaning by tag (`cache:redis-clear-tags` command)
- Do not cache resource types, callbacks, or entity objects; cache simple types or arrays
- Custom aspects and cache logic supported (extend `CacheTrait`, `CachebleAspect`, `CachePutAspect`)

## Contributing

1. Ensure code passes tests before submitting issues or PRs
2. Follow PSR-12 coding style
3. Read core class docs and comments first

## License

- MIT License
- (c) tourze

## Changelog

See [CHANGELOG.md]
