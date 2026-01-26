---
description: Check if a file is dead code or currently used.
---
To audit a file (e.g., `OldController.php`):
1. **Route Check:** `php artisan route:list | grep OldController`
2. **Usage Check:** `grep -r "OldController" app/ resources/`
3. **Report:**
   - 0 references? -> Suggest DELETE.
   - Referenced but unused? -> Suggest CLEANUP.
