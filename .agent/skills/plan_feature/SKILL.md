---
description: Analyze a request and create an architectural plan following the "Entity" protocols.
---

Step 1: **Investigate**
- Search for existing Services or Traits that might handle this logic.
- Run `ls -R` on `resources/js/Technologies` if it's a frontend task.

Step 2: **Polymorphic Analysis**
- Does this apply to `Entity` (Abstract) or specific Type?
- Does it need a Migration? (Remember: Metadata=SQL, Content=MongoDB).

Step 3: **Frontend Strategy**
- Which `Technology` module does this belong to?
- Which `Store` needs to be updated?

Step 4: **Output Plan**
- List the files to be modified.
- List the tests to be run.
