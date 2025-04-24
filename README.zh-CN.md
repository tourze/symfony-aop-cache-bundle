# AopCacheBundle

AopCacheBundle 是一个基于 Symfony 的缓存增强组件，利用 AOP（面向切面编程）技术，实现声明式、注解驱动的缓存能力。开发者可以通过简单的注解为方法添加缓存，支持缓存标签、TTL 控制、强制刷新等高级特性。

![Packagist Version](https://img.shields.io/packagist/v/tourze/symfony-aop-cache-bundle)
![License](https://img.shields.io/github/license/tourze/symfony-aop-cache-bundle)

## 功能特性

- 基于注解的声明式缓存 (`#[Cacheble]`, `#[CachePut]`)
- 支持 Twig 语法自定义缓存键
- 支持缓存标签，便于批量管理和清理
- 支持 TTL（过期时间）控制
- 支持强制刷新缓存
- 可扩展的切面与缓存逻辑

## 安装说明

- 依赖 PHP >= 8.1
- 依赖 Symfony >= 6.4
- 依赖 Redis 扩展

```shell
composer require tourze/symfony-aop-cache-bundle
```

## 快速开始

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

4. 缓存键模板说明：
   - 访问参数：`{{ paramName }}`
   - 访问连接点信息：`{{ joinPoint.method }}`、`{{ joinPoint.class }}`
   - 支持所有 Twig 语法

## 详细文档

- 支持缓存标签批量清理（命令：`cache:redis-clear-tags`）
- 不建议缓存资源类型、回调函数、实体对象，推荐缓存简单类型或数组
- 可自定义切面与缓存逻辑（继承 `CacheTrait`、`CachebleAspect`、`CachePutAspect`）

## 贡献指南

1. 提交 Issue 或 PR 前请确保代码通过测试
2. 遵循 PSR-12 编码规范
3. 推荐先阅读核心类文档和注释

## 版权和许可

- MIT License
- (c) tourze

## 更新日志

详见 [CHANGELOG.md]
