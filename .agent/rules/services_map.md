# Service Registry & Responsibilities

Before creating a new service, check if one exists:

| Service Name | Responsibility |
| :--- | :--- |
| **`EntityManagerService`** | CRUD for Entities (Create, Update, Delete, Restore). Handles Transactions. |
| **`EntityContentService`** | Handling MongoDB content (Segments, Pages, Blocks). |
| **`EntityRelationService`** | Managing Taxonomy (Tags, Categories) & Polymorphic connections. |
| **`MediaManagerService`** | Handling Physical Files (Upload, Storage, Deletion). |
| **`BookContentService`** | Specific logic for Book structures (Hierarchy, Chapters). |
| **`ReadingPositionService`** | User progress tracking. |
| **`EntityQueryService`** | Advanced Search & Filtering logic (replacing scopes). |

**Rule:** If a feature fits in one of these, ADD a method to it. Do NOT create a new Service.
