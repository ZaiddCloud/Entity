# Entity System Architecture

## The Polymorphic Core
- **Root:** `App\Models\Entity`.
- **Trait:** `HasPolymorphicRelations`: Governs `tags`, `authors`, `versions`, `categories`, `collections`.
- **Note:** Always check `$entity->type` before accessing child relationships (e.g., don't call `$entity->book` if type is `video`).

## Observer Layer
- **`EntityLifecycleObserver`:** Handles UUIDs and Slugs. DO NOT generate slugs manually.
- **`EntityCacheObserver`:** Invalidates polymorphic caches.
