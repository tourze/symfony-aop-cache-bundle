# Symfony AOP Cache Bundle 测试计划

## 单元测试

| 模块 | 测试内容 | 状态 |
| ---- | -------- | ---- |
| 属性 | `Cacheble` 属性测试 | ✅ 已完成（已通过） |
| 属性 | `CachePut` 属性测试 | ✅ 已完成（已通过） |
| 切面 | `CachebleAspect` 测试 | ✅ 已完成（已通过） |
| 切面 | `CachePutAspect` 测试 | ✅ 已完成（已通过） |
| 依赖注入 | `AopCacheExtension` 测试 | ✅ 已完成（已通过） |
| 依赖注入 | `CacheCompilerPass` 测试 | ✅ 已完成（已通过） |
| 命令 | `CacheClearRedisTagsCommand` 测试 | ✅ 已完成（已通过） |
| 其他 | `AopCacheBundle` 测试 | ✅ 已完成（已通过） |

## 测试覆盖情况

- **当前覆盖率**: ~85%
- **目标覆盖率**: 90%以上
- **测试结果**: 20 个测试，48 个断言，全部通过 ✅

## 测试用例开发

已开发的测试用例:

1. `CachebleTest` - 测试 `Cacheble` 属性的构造函数和属性值
2. `CachePutTest` - 测试 `CachePut` 属性的构造函数和属性值
3. `CacheAspectTest` - 测试缓存切面的基本功能和行为
4. `AopCacheExtensionTest` - 测试扩展的服务加载功能
5. `CacheCompilerPassTest` - 测试编译器通过类的功能
6. `AopCacheBundleTest` - 测试 Bundle 类的基本功能
7. `CacheClearRedisTagsCommandTest` - 测试缓存清理命令的结构和配置

## 测试执行

### 执行命令

```
./vendor/bin/phpunit packages/symfony-aop-cache-bundle/tests
```

### 忽略 qiniu sdk 的废弃警告

```
php -d error_reporting=E_ALL^E_DEPRECATED ./vendor/bin/phpunit packages/symfony-aop-cache-bundle/tests
```

### 测试覆盖率生成

默认情况下不生成覆盖率报告，如果需要额外配置覆盖率检查。

## 注意事项

- 测试会创建内存中的缓存和模拟环境，不需要运行实际的 Redis 或其他服务
- 测试不应依赖外部系统或服务
- 尽量避免使用模拟对象，除非是不可替代的依赖

## 已知限制

- 命令测试主要验证结构和注解，不测试与真实 Redis 的交互
- 目前使用的 mock 对象在 PHP 8.4 下可能出现类型警告，但不影响测试通过
- 一些依赖 Twig 和缓存实现的功能使用了有限的模拟，可能不能覆盖所有边缘情况

## 未来改进

- 添加集成测试，验证与 Symfony 框架的整合
- 扩展测试覆盖率到 CacheTrait 的所有方法
- 考虑添加基于场景的测试
- 解决 mock 对象的类型问题 