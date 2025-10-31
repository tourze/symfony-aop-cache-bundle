# AopCacheBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-aop-cache-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-cache-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/symfony-aop-cache-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-cache-bundle)
[![License](https://img.shields.io/github/license/tourze/symfony-aop-cache-bundle?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/monorepo/test.yml?style=flat-square)](https://github.com/tourze/monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/monorepo?style=flat-square)](https://codecov.io/gh/tourze/monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-aop-cache-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-cache-bundle)

AopCacheBundle 是一个基于 Symfony 的缓存增强组件，利用 AOP（面向切面编程）技术，
实现声明式、注解驱动的缓存能力。开发者可以通过简单的注解为方法添加缓存，
支持缓存标签、TTL 控制、强制刷新等高级特性。

## 功能特性

- 基于注解的声明式缓存 (`#[Cacheble]`, `#[CachePut]`)
- 支持 Twig 语法自定义缓存键
- 支持缓存标签，便于批量管理和清理
- 支持 TTL（过期时间）控制
- 支持强制刷新缓存
- 可扩展的切面与缓存逻辑

## 依赖要求

此包需要以下依赖：

- PHP >= 8.1
- Symfony Framework Bundle >= 6.4
- Symfony Cache Component >= 6.4
- Redis PHP 扩展
- Twig 模板引擎
- AOP Bundle 用于面向切面编程支持

## 安装说明

**通过 Composer 安装：**

```bash
composer require tourze/symfony-aop-cache-bundle
```

**启用 Bundle：**

```php
// config/bundles.php
return [
    // ...
    Tourze\Symfony\AopCacheBundle\AopCacheBundle::class => ['all' => true],
    // ...
];
```

## 配置说明

Bundle 自动与 Symfony 的缓存配置集成。确保缓存配置正确：

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

## 快速开始

### 基本用法

1. 在方法上添加 `#[Cacheble]` 注解：

```php
use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;

class UserService
{
    #[Cacheble(ttl: 3600, tags: ["user"])]
    public function getUserProfile(int $userId): array
    {
        // 业务逻辑
    }
}
```

2. 支持自定义缓存键与标签：

```php
#[Cacheble(key: "profile_{{ userId }}", tags: ["profile", "user_{{ userId }}"])]
public function getUserProfile(int $userId): array
{
    // ...
}
```

3. 强制刷新缓存：

```php
use Tourze\Symfony\AopCacheBundle\Attribute\CachePut;

#[CachePut(key: "profile_{{ userId }}")]
public function updateProfile(int $userId, array $data): array
{
    // ...
}
```

## 缓存键模板

缓存键模板支持：
- 访问参数：`{{ paramName }}`
- 访问连接点信息：`{{ joinPoint.method }}`、`{{ joinPoint.class }}`
- 支持所有 Twig 语法

## 高级用法

## 缓存管理命令

Bundle 提供了按标签批量清理缓存的命令：

```bash
# 清理 Redis 缓存标签（默认每天 5:10 AM 运行）
php bin/console cache:redis-clear-tags
```

## 扩展缓存逻辑

您可以通过以下方式扩展 Bundle 的功能：

1. **自定义缓存切面**：继承 `CachebleAspect` 或 `CachePutAspect`
2. **自定义缓存 Trait**：使用 `CacheTrait` 实现可重用的缓存逻辑
3. **自定义属性**：实现 `CacheAttributeInterface`

## 最佳实践

- **支持的返回类型**：缓存简单类型（字符串、数字、数组）或可序列化对象
- **避免缓存**：资源类型、回调函数或复杂的实体对象
- **性能优化**：使用缓存标签进行高效的批量失效
- **自定义逻辑**：继承 `CacheTrait`、`CachebleAspect` 或 `CachePutAspect` 
  实现自定义行为

## 贡献指南

1. Fork 这个仓库
2. 创建功能分支
3. 确保所有测试通过：`phpunit`
4. 遵循 PSR-12 编码规范
5. 提交 Pull Request

详细的贡献指南请参考 [CONTRIBUTING.md](CONTRIBUTING.md)。

## 版权和许可

The MIT License (MIT). 详情请参考 [License File](LICENSE)。

## 更新日志

版本历史和变更详见 [CHANGELOG.md](CHANGELOG.md)。
