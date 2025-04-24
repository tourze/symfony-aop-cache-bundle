# Workflow Diagram

Below is a Mermaid diagram illustrating the main workflow of the AopCacheBundle:

```mermaid
flowchart TD
    A[Method Call with #[Cacheble]] --> B{Cache Key Exists?}
    B -- Yes --> C[Return Cached Value]
    B -- No  --> D[Execute Method]
    D --> E[Store Result in Cache]
    E --> F[Return Result]
    
    G[Method Call with #[CachePut]] --> H[Execute Method]
    H --> I[Force Store Result in Cache]
    I --> J[Return Result]
```

## Description

- For methods annotated with `#[Cacheble]`, the workflow checks if a cache key exists. If yes, it returns the cached value; if no, it executes the method and caches the result.
- For methods annotated with `#[CachePut]`, the method is always executed and the result is forcibly written to cache, regardless of existing cache.
