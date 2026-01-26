# Refactor Magic Strings to Enums

## Problem Statement

The codebase violates **Rule #4** from `.agent/system.prompt`:
> **Type Safety:** PROHIBITED to use raw strings like 'book'. Use Enums or Model Class Constants.

Currently, entity types (`'book'`, `'audio'`, `'video'`, `'manuscript'`) are hardcoded as magic strings throughout the codebase, leading to:
- ❌ Potential typos and runtime errors
- ❌ No IDE autocomplete support
- ❌ Difficult refactoring and maintenance
- ❌ Lack of type safety

## Proposed Changes

### Phase 1: Create Enums

#### [NEW] [EntityType.php](file:///home/z/PhpstormProjects/Entity/app/Enums/EntityType.php)
#### [NEW] [ContentNodeType.php](file:///home/z/PhpstormProjects/Entity/app/Enums/ContentNodeType.php)

### Phase 2: Update Services
- [MediaManagerService.php](file:///home/z/PhpstormProjects/Entity/app/Services/MediaManagerService.php)
- [EntityContentService.php](file:///home/z/PhpstormProjects/Entity/app/Services/EntityContentService.php)
- [EntityManagerService.php](file:///home/z/PhpstormProjects/Entity/app/Services/EntityManagerService.php)

### Phase 3: Update Controllers
- [ReaderController.php](file:///home/z/PhpstormProjects/Entity/app/Http/Controllers/ReaderController.php)
- [UnifiedEditorController.php](file:///home/z/PhpstormProjects/Entity/app/Http/Controllers/UnifiedEditorController.php)

### Phase 4: Update Requests & Commands
- [StoreEntityRequest.php](file:///home/z/PhpstormProjects/Entity/app/Http/Requests/StoreEntityRequest.php)
- [SyncStorage.php](file:///home/z/PhpstormProjects/Entity/app/Console/Commands/SyncStorage.php)
- [SeedRealisticData.php](file:///home/z/PhpstormProjects/Entity/app/Console/Commands/SeedRealisticData.php)

## Verification Plan
1. Run full test suite: `php artisan test`
2. Manual verification of entity creation and editing via UI.
