<?php

namespace Tourze\Symfony\AopCacheBundle\Aspect;

use Symfony\Contracts\Cache\ItemInterface;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\DoctrineHelper\EntityDetector;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;

trait CacheTrait
{
    private function getAttribute(JoinPoint $joinPoint): ?Cacheble
    {
        $method = new \ReflectionMethod($joinPoint->getInstance(), $joinPoint->getMethod());
        /** @var \ReflectionAttribute[] $attributes */
        $attributes = $method->getAttributes(Cacheble::class);
        if (empty($attributes)) {
            // 这里返回null，则不进行缓存处理
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * 缓存key
     */
    private function buildKey(JoinPoint $joinPoint): ?string
    {
        $attribute = $this->getAttribute($joinPoint);
        if (!$attribute) {
            return null;
        }
        // 如果没声明缓存key的话，我们根据方法名/参数自动生成一个
        $key = $attribute->key ?: $joinPoint->getUniqueId();

        $template = $this->twig->createTemplate($key);
        return 'cache_'
            . trim($template->render([
                'joinPoint' => $joinPoint,
                ...$joinPoint->getParams(),
            ]));
    }

    /**
     * 缓存TTL
     */
    private function getTTL(JoinPoint $joinPoint): ?int
    {
        $attribute = $this->getAttribute($joinPoint);
        $ttl = $attribute?->ttl;
        return $ttl ?: 60;
    }

    /**
     * 获取缓存标签
     */
    private function getTags(JoinPoint $joinPoint): ?array
    {
        $attribute = $this->getAttribute($joinPoint);
        if (!$attribute) {
            return null;
        }

        $tags = $attribute->tags;
        if (!$tags) {
            return null;
        }

        foreach ($tags as $k => $v) {
            // 兼容标签就是类名的情形
            if (class_exists($v)) {
                $v = CacheHelper::getClassTags($v);
            } else {
                $template = $this->twig->createTemplate($v);
                $v = $template->render([
                    'joinPoint' => $joinPoint,
                    ...$joinPoint->getParams(),
                ]);
            }
            $tags[$k] = $v;
        }
        return $tags;
    }

    private function couldResultSave(mixed $var): bool
    {
        // 目前已知的，需要忽略缓存的部分返回值
        if (is_resource($var) || is_callable($var)) {
            return false;
        }
        // 实体需要忽略，问题比较多
        if (is_object($var) && EntityDetector::isEntityClass($var)) {
            return false;
        }
        return true;
    }

    /**
     * 一般用于在结束执行后，持久化结果到缓存
     */
    private function persistCache(JoinPoint $joinPoint, string $key): void
    {
        $result = $joinPoint->getReturnValue();
        if (!$this->couldResultSave($result)) {
            return;
        }

        $this->cache->get($key, function (ItemInterface $item) use ($result, $joinPoint) {
            $ttl = $this->getTTL($joinPoint);
            if ($ttl !== null) {
                $item->expiresAfter($this->getTTL($joinPoint));
            }

            $tags = $this->getTags($joinPoint);
            if ($tags !== null) {
                $item->tag($tags);
            }

            $item->set($result);
            return $result;
        });
    }
}
