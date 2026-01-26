---
description: Run the correct test suite for the Entity project
---

- **Logic/Service:** `php artisan test --testsuite=Unit`
- **Controller/Route:** `php artisan test --testsuite=Feature`
- **Browser/UI:** `php artisan dusk` (Only if requested)

**Specific Paths:**
- Manuscripts: `tests/Feature/ManuscriptCreationIntegrationTest.php`
- Books: `tests/Feature/BookWorkflowTest.php`
