# AopCacheBundle

AopCacheBundle 是一个基于 Symfony 的缓存实现包,通过 AOP 技术提供声明式的缓存能力。它允许开发者使用简单的注解来为方法添加缓存功能,支持缓存标签、TTL 控制和强制更新。

## 核心功能

### 声明式缓存

使用 `#[Cacheble]` 注解标记需要缓存的方法:

```php
#[Cacheble]
public function getUserProfile(int $userId): array 
{
    // 此方法的结果会被缓存
    // ...
}
```

### 自定义缓存键

支持使用 Twig 模板语法定义缓存键:

```php
#[Cacheble(key: "profile_{{ userId }}")]
public function getUserProfile(int $userId): array 
{
    // 使用 userId 作为缓存键的一部分
    // ...
}
```

### 缓存标签

支持为缓存添加标签,便于批量管理:

```php
#[Cacheble(tags: ["user", "profile_{{ userId }}"])]
public function getUserProfile(int $userId): array 
{
    // 使用多个标签
    // ...
}
```

### 强制更新缓存

使用 `#[CachePut]` 注解强制更新缓存:

```php
#[CachePut(key: "profile_{{ userId }}")]
public function updateUserProfile(int $userId, array $data): array 
{
    // 结果会被强制写入缓存
    // ...
}
```

## 使用方法

1. 在方法上添加 `#[Cacheble]` 注解:

```php
use AopCacheBundle\Attribute\Cacheble;

class UserService 
{
    #[Cacheble(ttl: 3600, tags: ["user"])]
    public function getUserProfile(int $userId): array 
    {
        // 业务逻辑
    }

    #[CachePut(key: "profile_{{ userId }}")]
    public function updateProfile(int $userId, array $data): array 
    {
        // 更新逻辑
    }
}
```

2. 缓存键模板支持:
   - 访问方法参数: `{{ paramName }}`
   - 访问连接点信息: `{{ joinPoint.method }}`, `{{ joinPoint.class }}`
   - 支持所有 Twig 语法特性

## 重要说明

1. 缓存限制
   - 不支持缓存资源类型
   - 不支持缓存回调函数
   - 不支持缓存实体对象
   - 建议缓存简单数据类型或普通数组

2. 缓存标签
   - 支持使用类名作为标签
   - 支持使用 Twig 模板生成标签
   - 便于批量清理相关缓存

3. 性能考虑
   - 缓存键的生成会有少量开销
   - 复杂的 Twig 模板可能影响性能
   - 建议为频繁访问的方法添加缓存

4. 缓存清理
   - 可以通过标签批量清理缓存
   - 建议设置合理的 TTL
   - 考虑使用 `CachePut` 在更新时刷新缓存

## 扩展开发

1. 自定义缓存处理
   - 继承 `CacheTrait` 
   - 实现自定义的缓存逻辑

2. 自定义切面
   - 继承 `CachebleAspect` 或 `CachePutAspect`
   - 重写相关方法以自定义缓存行为

## 调试建议

1. 开启日志记录以跟踪缓存操作
2. 使用缓存调试工具查看缓存状态
3. 监控缓存命中率
