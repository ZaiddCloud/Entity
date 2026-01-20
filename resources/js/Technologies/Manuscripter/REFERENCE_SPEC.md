# Manuscripter Technology - Reference Specification 📜

**Status:** Legacy Monolith (V1)
**File:** `ManuscriptClient.vue`
**Purpose:** This document serves as the "Source of Truth" for the functional and architectural details of the Manuscripter component. It must be consulted before any refactoring to prevent feature loss.

---

## 1. Core Architecture

### Inputs (Props)
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `manuscript` | Object | Required | The primary manuscript entity data. |
| `siblings` | Array | `[]` | List of related versions/copies (for comparison). |
| `activeSlug` | String | `null` | The slug of the currently selected page (from URL). |

### State Management
| State Variable | Type | Description |
|----------------|------|-------------|
| `shotNumber` | Integer | Current page index (1-based). Synced with `activeSlug`. |
| `viewMode` | String | Controls the main layout: `'list'` (Vertical), `'grid'` (Thumbnails), `'default'` (Metadata Table). |
| `isCompareMode` | Boolean | Toggle for side-by-side version comparison. Overrides `'list'` behavior. |
| `selectedVersionIds` | Array | IDs of versions to display. In single mode = 1 ID; Compare mode = multiple. |
| `panelWidths` | Array | Stores dynamic width percentages for split panes in Compare Mode. |
| `windowWidth` | Number | Used for responsive filmstrip calculations. |

---

## 2. Complex Logic & Behaviors

### 🔄 Navigation & Sync
- **Two-Way Binding:**
  - If `shotNumber` changes (via Input or Filmstrip) → Emits `navigate` event with the new slug.
  - If `activeSlug` changes (via Browser Back/Inertia) → Updates `shotNumber`.
- **Debouncing Strategy:**
  - Code notes mention: "Debounce slightly or just emit. Inertia handles visits well."
  - Implementation currently emits immediately on change, relying on Inertia to handle rapid navigation cancellation.
- **Filename Extraction:**
  - Uses a robust `try-catch` block to parse filenames from URLs.
  - Splits by `/` then `.` to get the clean base name.
  - Returns `'N/A'` on failure to prevent crashes.

### 📐 Resizing Logic (Compare Mode)
A custom implementation of split-pane resizing without external libraries:
- **Event Listeners:** `mousemove` and `mouseup` attached to `window` during drag.
- **RTL Awareness:** Calculates delta based on `-movementX` because the layout is RTL.
- **Constraints:** Prevents panels from shrinking below `10%`.
- **Distribution:** When versions change, widths are reset to equal distribution (`100 / n`).

### 📚 Versions & Comparison
- **Derived Versions:** `versions` computed property merges `props.manuscript` (Current) with `props.siblings`.
- **Selection Logic:**
  - **Single Mode:** Clicking a version replaces `selectedVersionIds` entirely.
  - **Compare Mode:** Clicking adds/removes from array. Prevents deselecting the last remaining item.

---

## 3. UI Zones & Layout

### A. The Viewport (`<main>`)
Occupies the full space (`z-0`) behind the floating HUDs.

1.  **Default View (Metadata Table):**
    - Rows showing Thumbnail, Shot Number, "Shot X", and Filename.
    - Click → Selects shot (`shotNumber = i`).
    - Double Click → Switches to `list` view & scrolls to shot (`scrollToShot(i)`).
    - Hover Actions: "View" button appears (`opacity-0` → `opacity-100`).

2.  **Grid View (Contact Sheet):**
    - Responsive Grid (cols: 4 to 10).
    - Hover Overlay: Shows Shot # with gradient background.
    - Active Indicator: Blue dot (`bg-blue-500`) with shadow at top-right for current shot.

3.  **List View (Reading Mode):**
    - **Single:** Vertical scroll of full-width images. Lazy loaded.
    - **Compare:** Horizontal Flex container.
        - Resizable dividers between panels with specific styling (`bg-white/10`, hover `bg-blue-500/50`).
        - "Minimal Filename Overlay" at bottom-right of each panel (`bg-black/40 backdrop-blur`).

### B. The Header HUD (`<header>`)
Absolute positioned top bar (`z-10`) with gradient background (`from-black/60`).

1.  **Right Section (Navigation):**
    - **Frequency Pills:** List of versions. Selected ones are Blue. Inputs show current shot # per version.
    - **Global Shot Input:** `input[type=number]` bound to `shotNumber`. Shows `Max` pages. Styled transparently with bottom border.

2.  **Left Section (View Options):**
    - **View Toggles:** Icons for List, Grid, Default. Active state uses `bg-indigo-500`.
    - **Compare Switch:** Text toggle "Single / Compare".

### C. The Footer HUD (`<footer>`)
Absolute positioned bottom bar (`z-10`).

1.  **Filmstrip:**
    - Horizontal scrollable list of numbers.
    - Arrows/keys navigation support (implied by native scroll).
    - Active shot is Blue/Highlighted (`ring-1 ring-blue-400`).
    - Hover Effect: Small translation up (`-translate-y-0.5`).

2.  **Controls:**
    - **Zoom:** `-` `100%` `+` buttons (Currently visual placeholders).
    - **Info:** Displays "Shot #X".

---

## 4. Critical Implementation Details

- **Scroll-to-Shot:** Uses `scrollIntoView({ block: 'center' })` inside a `setTimeout` (100ms) to ensure DOM render after view switch.
- **CSS Scrollbars:** Custom `.custom-scrollbar` class for styled scrolling in Webkit.
- **Z-Index Stacking:**
    - Content: `z-0`
    - Overlays (Header/Footer): `z-10`
    - Resize Handles: `z-50` (Ensures drag handle is always grab-able).
- **Responsive Logic:** `windowWidth` is tracked but mainly used for internal calculations (currently unused logic `filmstripCount` just returns `totalPages`, hinting at a simplified implementation).

---

### 🔍 Panning & Zooming (Viewer Capabilities)
*Found in `ManuscriptViewer.vue` variant*
- **Zoom Logic:** Supports `0.5x` to `3x` scale.
- **Pan Logic:** Uses `mousedown/move/up` to track mouse delta (`dx`, `dy`) and update `scrollLeft`/`scrollTop`.
- **UI:** Floating bubbles at bottom-left for Zoom In/Out/Reset.

---

## 3. UI Zones & Layout

### A. The Viewport (`<main>`)
Occupies the full space (`z-0`) behind the floating HUDs.

1.  **Default View (Metadata Table):**
    - Rows showing Thumbnail, Shot Number, "Shot X", and Filename.
    - Click → Selects shot (`shotNumber = i`).
    - Double Click → Switches to `list` view & scrolls to shot (`scrollToShot(i)`).
    - Hover Actions: "View" button appears (`opacity-0` → `opacity-100`).

2.  **Grid View (Contact Sheet):**
    - Responsive Grid (cols: 4 to 10).
    - Hover Overlay: Shows Shot # with gradient background.
    - Active Indicator: Blue dot (`bg-blue-500`) with shadow at top-right for current shot.

3.  **List View (Reading Mode):**
    - **Single:** Vertical scroll of full-width images. Lazy loaded.
    - **Compare:** Horizontal Flex container.
        - Resizable dividers between panels with specific styling (`bg-white/10`, hover `bg-blue-500/50`).
        - "Minimal Filename Overlay" at bottom-right of each panel (`bg-black/40 backdrop-blur`).

4.  **Detail Viewer Mode:**
    - *Features from `ManuscriptViewer.vue`*
    - **Header:** Uses "Glassmorphism" effect (`backdrop-filter: blur(12px)`).
    - **Toolbar:** Contains `ResourceNavigator` integration.
    - **Interaction:** Cursor becomes `grab` / `grabbing` during Panning.
