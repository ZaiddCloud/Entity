# Reader Technology - Reference Specification 📖

**Status:** 🚧 Planned (V1)
**Primary Component:** `BookReader.vue`
**Purpose:** This document serves as the "Source of Truth" for the functional and architectural design of the Reader component. It defines the reading experience for visitors consuming **all entity types** (Books, Manuscripts, Audio transcripts, Video transcripts) in a distraction-free environment.

> **⚠️ IMPORTANT:** This specification follows the architectural guidelines defined in [`.cursorrules`](file:///../../../.cursorrules), specifically **Section 5: Modular Construction (Frontend Rules)**. All implementations must adhere to the standard directory structure and isolation principles outlined there.

---

## 1. Core Architecture

### Inputs (Props)
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `entity` | Object | Required | The primary entity (Book/Manuscript/Audio/Video) with metadata and content hierarchy. |
| `activeSlug` | String | `null` | The slug of the currently active content node (chapter/page). |
| `readingPosition` | Object | `null` | Saved reading position (node slug + scroll offset). |

### State Management
| State Variable | Type | Description |
|----------------|------|-------------|
| `currentNode` | Object | The active content node being displayed (chapter, page, etc.). |
| `fontSize` | Number | Current font size in pixels (default: 18). Range: 12-32px. |
| `theme` | String | Active theme: `'light'`, `'dark'`, `'sepia'`. |
| `isFullscreen` | Boolean | Fullscreen reading mode toggle. |
| `isTocOpen` | Boolean | Table of Contents sidebar visibility. |
| `scrollProgress` | Number | Reading progress percentage (0-100). |
| `bookmarks` | Array | User's saved bookmarks (node slugs). |

---

## 2. Complex Logic & Behaviors

### 🔄 Navigation & Sync
- **Two-Way Binding:**
  - If `currentNode` changes (via TOC or Next/Prev) → Emits `navigate` event with new slug.
  - If `activeSlug` changes (via Browser Back/Inertia) → Updates `currentNode`.
- **Reading Position Persistence:**
  - Auto-saves scroll position every 5 seconds via debounced watcher.
  - Saves to localStorage: `reading_position_{entity_id}`.
  - On mount, restores position if available.
- **Progress Calculation:**
  - Tracks scroll position relative to total content height.
  - Updates `scrollProgress` on scroll (debounced 300ms).

### 📚 Content Hierarchy Navigation
- **Entity-Specific Structure:**
  - **Books:** Hierarchical (Sub-book → Part → Chapter).
  - **Manuscripts:** Flat (Sequential pages).
  - **Audio:** Flat (Sequential segments with timestamps).
  - **Video:** Flat (Sequential scenes/segments with timestamps).
- **Smart Next/Previous:**
  - Traverses hierarchy depth-first for Books.
  - Sequential navigation for Manuscripts/Audio/Video.
  - Skips container nodes (e.g., Parts) that have no direct content.
  - Wraps around at boundaries (optional setting).

### 🎨 Theme System
- **Presets:**
  - **Light:** White background, dark text, blue accents.
  - **Dark:** Dark gray background, light text, amber accents.
  - **Sepia:** Warm beige background, brown text, vintage feel.
- **CSS Variables:**
  - Each theme sets `--reader-bg`, `--reader-text`, `--reader-accent`.
  - Smooth transitions (300ms) on theme change.

### 🔍 Search Functionality
- **In-Document Search:**
  - Highlights all matches in current node.
  - Provides "Next/Previous Match" navigation.
  - Displays match count (e.g., "3 of 12").
- **Full-Text Search:**
  - Searches across all content nodes.
  - Returns list of matching nodes with context snippets.
  - Click → Navigates to node and highlights match.

### 🎵 Audio/Video Specific Features
- **Synchronized Transcript Display:**
  - Shows text content synchronized with media playback.
  - Auto-scrolls to current segment during playback.
  - Click on text → Seeks media to that timestamp.
- **Timestamp Navigation:**
  - Each segment displays its timestamp (e.g., "00:05:23").
  - Click timestamp → Jumps to that point in media.
- **Media Player Integration:**
  - Embedded mini-player at bottom (optional).
  - Can open full Player in new tab/window.
  - Playback controls: Play/Pause, Speed, Volume.
- **Transcript-Only Mode:**
  - Option to read transcript without media player.
  - Useful for quick scanning or accessibility.

---

## 3. UI Zones & Layout

### A. The Reading Viewport (`<main>`)
Occupies full viewport with responsive padding.

1.  **Content Area:**
    - Centered column (max-width: 800px for optimal readability).
    - Line height: 1.8 for comfortable reading.
    - Text alignment: Justified (Arabic) or Left (English).
    - Smooth scroll behavior.

2.  **Lazy Loading:**
    - Renders current node + 1 node ahead/behind.
    - Unloads nodes outside viewport to optimize memory.

### B. The Header HUD (`<header>`)
Sticky top bar with auto-hide on scroll down.

1.  **Left Section:**
    - **Back Button:** Returns to entity detail page.
    - **Entity Title:** Displays book/manuscript name.

2.  **Center Section:**
    - **Progress Bar:** Visual indicator of reading progress.
    - **Chapter Title:** Current node title (truncated).

3.  **Right Section:**
    - **TOC Toggle:** Opens/closes table of contents.
    - **Settings Menu:** Font size, theme, fullscreen.

### C. The Table of Contents Sidebar
Slide-in panel (right side for RTL).

1.  **Hierarchy Display:**
    - Nested list with expand/collapse icons.
    - Active node highlighted with accent color.
    - Scroll-to-active on open.

2.  **Quick Actions:**
    - **Search:** Filter TOC by title.
    - **Bookmarks Tab:** Switch to bookmarks view.

### D. The Footer Controls (`<footer>`)
Fixed bottom bar (auto-hide on scroll).

1.  **Navigation:**
    - **Previous Chapter:** Left arrow button.
    - **Next Chapter:** Right arrow button.
    - Disabled state when at boundaries.

2.  **Reading Info:**
    - **Time Remaining:** Estimated reading time for current node.
    - **Page Count:** "Page X of Y" (for manuscripts).

---

## 4. Critical Implementation Details

### Scroll Restoration
- Uses `scrollIntoView({ block: 'start', behavior: 'smooth' })` on navigation.
- Waits for DOM update via `nextTick()` before scrolling.
- Stores scroll offset in `sessionStorage` for browser back/forward.

### Keyboard Shortcuts
| Key | Action |
|-----|--------|
| `←` / `→` | Previous/Next chapter |
| `F` | Toggle fullscreen |
| `T` | Toggle TOC |
| `+` / `-` | Increase/Decrease font size |
| `Ctrl+F` | Open search |
| `B` | Add bookmark |

### Responsive Breakpoints
- **Mobile (< 768px):**
  - Reduced padding.
  - Simplified header (icons only).
  - TOC becomes full-screen overlay.
- **Tablet (768px - 1024px):**
  - Standard layout with adjusted max-width.
- **Desktop (> 1024px):**
  - Full feature set.
  - Optional dual-pane mode (TOC always visible).

### Accessibility
- **ARIA Labels:** All interactive elements labeled.
- **Focus Management:** Keyboard navigation fully supported.
- **Screen Reader:** Content structure announced properly.
- **High Contrast:** Theme system supports high contrast mode.

---

## 5. Data Flow & Integration

### Backend Requirements
- **Endpoint:** `GET /reader/{type}/{slug}`
  - `{type}`: `book`, `manuscript`, `audio`, `video`
- **Response:**
  ```json
  {
    "entity": { 
      /* Book/Manuscript/Audio/Video metadata */
      "id": 1,
      "type": "book",
      "title": "...",
      "cover_path": "...",
      "duration": 3600  // For Audio/Video only
    },
    "content": { 
      /* Current node content (Tiptap JSON) */
      "type": "doc",
      "content": [...]
    },
    "hierarchy": [ 
      /* Flat or nested TOC structure */
      /* For Audio/Video: includes timestamps */
      {
        "id": 1,
        "slug": "segment-1",
        "title": "Introduction",
        "start_time": 0,      // Audio/Video only
        "end_time": 120       // Audio/Video only
      }
    ],
    "readingPosition": { 
      /* Saved position if exists */
      "node_slug": "chapter-3",
      "scroll_offset": 450,
      "timestamp": 125  // For Audio/Video only
    },
    "mediaSource": "/storage/audio/file.mp3"  // For Audio/Video only
  }
  ```

### Store Integration
- **ReaderStore.js** (in `Technologies/Reader/Core/`):
  - Manages reading state (position, settings).
  - Syncs with localStorage.
  - Provides actions for navigation, bookmarks, search.

### Component Structure
```
Technologies/Reader/
├── Core/
│   └── ReaderStore.js          # State management
├── UI/
│   ├── BookReader.vue          # Main component
│   ├── ContentView.vue         # Renders Tiptap content
│   ├── TableOfContents.vue     # TOC sidebar
│   ├── ReadingControls.vue     # Settings panel
│   ├── SearchPanel.vue         # Search interface
│   └── ProgressBar.vue         # Reading progress indicator
└── Themes/
    ├── LightTheme.js
    ├── DarkTheme.js
    └── SepiaTheme.js
```

---

## 6. Future Enhancements (V2)

- **Annotations:** Highlight text and add notes.
- **Text-to-Speech:** Audio playback of content.
- **Offline Mode:** Download for offline reading (PWA).
- **Reading Statistics:** Track reading time, pages read.
- **Social Features:** Share quotes, reading lists.
- **Multi-Language:** Support for bilingual texts.

---

## 7. Design Philosophy

The Reader is designed with these principles:

1.  **Distraction-Free:** Minimal UI that fades away during reading.
2.  **Accessibility-First:** Usable by everyone, regardless of ability.
3.  **Performance:** Instant navigation, smooth scrolling.
4.  **Customization:** Readers control their experience.
5.  **Respect for Content:** Typography and layout honor the text.

---

**Last Updated:** 2026-01-25
**Maintainer:** Entity Development Team
