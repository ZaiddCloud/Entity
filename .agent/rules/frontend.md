# Frontend Modular Architecture

## Directory Structure
- **`resources/js/Technologies/`**: Domain Modules (Editors, Players).
    - `Store/`: Global shared state.
    - `{Module}/Core/`: Private logic/state.
    - `{Module}/UI/`: Dumb components.
- **`resources/js/Components/`**: Generic UI (Buttons, Inputs) ONLY.

## Interaction Rules
1. **Isolation:** Modules must NOT import from each other. Use Shared Stores.
2. **Props over Fetch:** Components receive data via Inertia Props.
3. **Editors:** We use Tiptap. Configurations are in `Technologies/Editor`.
