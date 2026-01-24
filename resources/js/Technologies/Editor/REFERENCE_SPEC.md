# Editor Technology - Reference Specification 📜

**Status:** Stable (V1)
**Stack:** Tiptap (Vue 3) + Pinia + TailwindTypography
**Purpose:** A polymorphic, rich-text editor supporting complex scholarly content (Manuscripts, Books, Media) with domain-specific extensions.

---

## 1. Core Architecture

### The Brain: `EditorStore.js` (Pinia)
Manages the global state of the editing session.
- **Polymorphism:** Handles `book`, `manuscript`, `audio`, `video`.
- **State:**
  - `currentEntity`: Parent Model (e.g., Book).
  - `currentContentNode`: Child Node (e.g., Page/Segment) containing the HTML content.
  - `content`: Reactive HTML string synced with Tiptap.
  - `editorMode`: Determines distinct behaviors/UI (e.g., `audio` mode shows segment editor).
- **Actions:**
  - `loadDocument()`: Initializes the session.
  - `save()`: Handles AJAX persistence to `/studio/{type}/{slug}/save`.
  - `startAutoSave()`: Interval-based check (30s) for `hasUnsavedChanges`.

### The Heart: `TiptapEditor.vue`
Wraps the Tiptap instance and configures extensions.
- **Configuration:**
  - **Typography:** `StarterKit` (H1-H6), `Underline`, `TextAlign` (RTL default), `Highlight`, `Color`.
  - **Structure:** `Table`, `TableRow`, `TableCell`, `TableHeader` (Resizable).
  - **Media:** `Image`, `FileNode` (Custom).
  - **Domain:** `HeritagePoetry`, `QuranicVerse`, `ScientificFootnote`.
  - **UX:** `Placeholder`, `Link`, `Subscript`, `Superscript`.
  - **Interaction:** `DragAndDrop`, `DragHandle`, `CommandExtension` (Slash Menu).
- **Events:**
  - `handleClick`: Intercepts clicks on Footnotes to open the specific `FootnoteStore` editor.

### The Executioner: `TiptapStore.js` (Pinia)
A specialized store for handling editor commands and instance references.
- **Role:** Pure Command Pattern implementation.
- **Actions:**
  - `executeCommand(command, value)`: Maps strings (e.g., 'bold', 'insertTable') to Tiptap chain commands.
  - `isActive(name, attrs)`: Checks current selection state.
  - **Extended Commands:** Includes Table operations (`mergeCells`, `splitCell`), Link management, and custom extension triggers.

---

## 2. UI Layout & Components

### The Wrapper: `EditorClient.vue`
The entry point for the "Studio" page.
- **Role:** Layout orchestrator.
- **Features:**
  - **Toolbar:** `<EditorToolbar />` (Top fixed bar, uses Glassmorphism).
  - **Float/Dock System:** Wraps media players (Audio/Video) in `<ReferencePane />` allowing them to be `sticky` or `fixed` (Floating).
  - **Extensions UI:** Renders global UI for extensions like `<FootnoteEditor />`.

### The Toolbar: `EditorToolbar.vue`
Modular component using "Group" sub-components.
- **Groups:**
  - `HistoryGroup`: Undo/Redo.
  - `StructureGroup`: H1-H6, Paragraph.
  - `FormattingGroup`: Bold, Italic, Underline.
  - `ListGroup`: Bullet/Ordered lists.
  - `BlockGroup`: Blockquote, CodeBlock.
  - `InsertGroup`: Link, Image, Table.
  - `TextAlignGroup`: Alignment controls.
  - `ScientificGroup`, `HeritageGroup`: Domain specific buttons.
- **Logic:**
  - **Pinning:** Can toggle `sticky` state via `togglePin`.
  - **Save Feedback:** Visual state (`idle` -> `saving` -> `saved`).

### Modals & Dialogs (`Modals/`)
- `ExportModal.vue`: Handles document export options.
- `FootnoteModal.vue`: Editor for scientific footnotes.
- `PoetryModal.vue` & `QuranicVerseModal.vue`: Input forms for structured content insertion.

### Custom Extensions (Domain Specific)
1.  **Heritage Poetry (`PoetryExtension`)**
    - Block-level visualization for Arabic poetry (Sadr/Ajuz).
    - Uses `PoetryModal` for structured input.
2.  **Quranic Verse (`QuranExtension`)**
    - Special styling and font (`Amiri Quran`, `Hafs`).
    - Uses `QuranicVerseModal` to fetch/insert verses.
3.  **Scientific Footnotes (`FootnoteExtension`)**
    - Inline markers (e.g., `[1]`) that link to metadata.
    - Managed by `FootnoteStore` and `<FootnoteEditor>` modal.
4.  **Slash Commands (`CommandExtension`)**
    - Triggered by `/`.
    - Shows generic and domain suggestions (`suggestionUtils`).
5.  **Drag & Drop**
    - `DragHandleExtension`: Draggable "⋮⋮" handle for blocks.
    - `FileNode`: Custom node for dropped files (PDFs/Images).
    - **Logic:** `DragAndDropExtension` handles file upload/parsing events.

---

## 3. Styling & Typography (`index.css`)

### RTL & Araic Support
- **Direction:** Global `dir="rtl"`.
- **Fonts:** `Amiri` (Body), `Traditional Arabic` (Headings).
- **Prose:** heavily customized `@tailwindcss/typography` (`prose-lg`).
  - `text-align: right` enforced.
  - `line-height: 2` for readability.

### visual Hierarchy
- **Paper Effect:** The editor container mimics a physical paper sheet with `box-shadow` and `min-height`.
- **Headings:** Bold, varying sizes (H1: 2em, H2: 1.5em).
- **Interactive:** Hover states for Drag Handles (`opacity: 0` -> `1`).

---

## 4. Critical Logic Flows

### The Save Flow
1.  **Trigger:** `Ctrl+S`, Toolbar Button, or AutoSave.
2.  **Payload:**
    - `html`: For display/SSR.
    - `json`: For Tiptap reconstruction.
    - `text`: For search indexing.
3.  **Endpoint:** `POST /studio/{type}/{slug}/save`.
4.  **Feedback:** Updates `lastSaved` timestamp on success.

### The Navigation Flow
1.  **Toolbar:** `prev`/`next` buttons use `store.navigation`.
2.  **Router:** Uses Inertia `router.visit()` to reload the Studio with new slug.
3.  **State:** `loadDocument()` re-runs on mount to hydrate new data.
