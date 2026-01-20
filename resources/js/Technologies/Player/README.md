# Media Player Technology 🎥

**Documentation Status:** ✅ Up to Date (Modular V2 Architecture)

The **Media Player** module provides a high-fidelity, draggable, and resizable media player that replicates the experience of the V1 "PotPlayer" while adhering to a modern, modular Vue.js architecture. It is designed for seamless integration with the Studio Editor, supporting both Audio and Video content with specialized behaviors.

## 🏗 Modular Architecture

The player has been refactored from a monolithic file into focused, single-responsibility components:

### Core Components
| Component | Description |
|-----------|-------------|
| **`MediaPlayer.vue`** | The main orchestrator. Handles window positioning, sizing, and assembles the UI sub-components. |
| **`PlayerClient.vue`** | The integration layer. Connects the player to the Inertia app, handles props, and manages segment navigation logic. |

### UI Components (`/UI`)
| Component | Description |
|-----------|-------------|
| **`PlayerHeader.vue`** | Manages window controls (Close, Maximize, Dock) and displays the title. Optimized for RTL/LTR layouts. |
| **`PlayerControls.vue`** | Contains all playback controls (Play/Pause, Seek, Volume, Speed, Segments, etc.). |
| **`VideoScreen.vue`** | Renders the `<video>` or `<audio>` element, along with the visualizer and info overlay. |
| **`PlayerPlaylist.vue`** | Displays the list of media segments with duration and active state highlighting. |
| **`ResizeHandles.vue`** | Provides drag handles for resizing the floating window. |

### State Management
- **`Store/MediaStore.js`**: A Pinia store that centrally manages:
  - Window State (`isDocked`, `isMaximized`, `isFloating`)
  - Dimensions & Position (`width`, `height`, `left`, `top`)
  - Media State (`isPlaying`, `currentTime`, `volume`, `playbackRate`)
- **`Composables/useMedia.js`**: Encapsulates reactive logic for the HTML5 Media Element events.

---

## 🌟 Key Features (V1 Parity)

### 1. Window Management
- **Draggable:** Click and drag the header to move the player anywhere on screen.
- **Resizable:** Drag edges/corners to resize.
- **Modes:**
  - **Floating:** Free-floating window (Default for Audio/Video).
  - **Docked:** Snaps to the side (Split View).
  - **Maximized:** Fullscreen within the browser window.

### 2. Smart Sizing & Positioning
- **Audio Mode:** Defaults to **240px** height (Compact).
- **Video Mode:** Defaults to **480px** height.
- **Initial Position:** Spawns at the **top-left** (20px, 130px) to respect Arabic text editing layouts.
- **Z-Index Strategy:** Uses `z-[999999]` to ensure it always floats above the Studio Toolbar.

### 3. Deep Integration
- **Segment Navigation:** Clicking a segment in the playlist directly navigates the Editor to the corresponding content node via Inertia.
- **RTL Support:**
  - The Header uses `dir="rtl"` for Arabic titles.
  - Controls and Timelines use `dir="ltr"` for standard media control flow.

### 4. Legacy "PotPlayer" Aesthetics
- Includes the signature album art gradient mask.
- Replicates specific font sizes, colors (`#eab308` yellow accents), and hover states.
- Support for keyboard shortcuts (Space, Arrows, M, F).

---

## 🛠 Usage

### Integration
The player is typically invoked via `PlayerClient.vue` within the Studio layout:

```vue
<PlayerClient
    :type="'audio'" 
    :media="mediaObject"
    :active-slug="currentSlug"
    :is-integrated="false"
/>
```

### Events
The `MediaPlayer` emits events for layout changes, which `PlayerClient` or the parent layout should handle:
- `@toggle-dock`
- `@close`
- `@segment-change`: Emits the selected segment object (used for navigation).

---

## ⚠️ Maintenance Notes
- **Z-Index**: If the player falls behind new UI elements, check `MediaPlayer.vue` root classes. Currently set to `z-[999999]`.
- **RTL**: `PlayerHeader.vue` has a specific DOM order swapped to accommodate `flex-row` with `dir="rtl"`. Be careful when reordering.
