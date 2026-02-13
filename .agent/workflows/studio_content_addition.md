---
description: Studio Content Addition (Step-by-Step) 🧱
---

🚨 THE CONSTITUTION (دستور العمل) 🚨
Strict Adherence Required: All steps below must be executed in total submission to the rules defined in the .agent directory, specifically:

The Atomic Encyclopedia (the_atomic_encyclopedia_of_master.md): For cognitive alignment, architectural truths, and "Let's Discuss" protocol. The System Prompt (system.prompt): For executive behavior and tone. Minimalism (التقليلية): Do NOT create any new file unless it is a matter of absolute extreme necessity. Leverage existing files and patterns first. This plan is a derivative of these core documents. Any deviation is a critical failure.

Step 1: StudioContentStructureTest (The Harmonic Contract) 📜
Goal Define the behavior of ContentNodeType for ALL Entities ensuring "Structural Harmony" (الانسجام الهيكلي) where identical types behave identically, and markers share unified traits.

Reverse Engineering Workflow (TDD) Create Test: 
tests/Browser/Studio/StudioContentStructureTest.php

Define Expectations (Harmonic Logic):

Book (The Standard): Allowed: [SUB_BOOK, PART, BAB, CHAPTER, MASALAH, SECTION, PAGE] Visuals: SUB_BOOK -> ['tag' => 'h1', 'behavior' => 'container'] PART -> ['tag' => 'h2', 'behavior' => 'container'] BAB -> ['tag' => 'h3', 'behavior' => 'container'] CHAPTER -> ['tag' => 'h4', 'behavior' => 'container'] MASALAH -> ['tag' => 'h5', 'behavior' => 'container'] SECTION -> ['tag' => 'h6', 'behavior' => 'container']

Manuscript (The Super-Hybrid): Allowed: ALL Book Types + [FOLIO] (Note: Page/Section are shared). Visuals: Inherited: PART, CHAPTER, etc. must match Book behavior exactly. Hybrid: SECTION matches Book SECTION (H6/Container) or overrides to H2? (Default to Book Harmony for now: H6). Markers: FOLIO -> ['tag' => 'h4', 'behavior' => 'marker'] PAGE -> ['tag' => 'h4', 'behavior' => 'marker'] (Override Book behaviors if needed).

Audio (Time-Based): Allowed: [SEGMENT, TRACK, MARKER] Visuals: SEGMENT -> ['tag' => 'h4', 'behavior' => 'marker'] TRACK -> ['tag' => 'h4', 'behavior' => 'marker'] MARKER -> ['tag' => 'h5', 'behavior' => 'marker']

Video (Time-Based): Allowed: [SEGMENT, SCENE, SHOT] Visuals: SCENE -> ['tag' => 'h4', 'behavior' => 'marker'] SHOT -> ['tag' => 'h5', 'behavior' => 'marker']

Run Test: (Expect Failure 🔴).

Implement Logic (Rule V.4): "المنطق ينتمي للخدمات (Services). الكنترولرز هي لتوجيه المسارات (Routing/Inertia) فقط." Modify 
app/Enums/ContentNodeType.php
 to implement allowedFor and getVisualMap.

Verify: (Expect Success 🟢).

Step 2: useStudioContentProcess (The Orchestrator) 🎼
Goal Create a unified Composable that orchestrates the "Universal Execution" across Editor, Player, and Backend.

Reverse Engineering Workflow (TDD) Create Test: 
tests/Browser/Studio/StudioContentProcessTest.php

Define Expectations (Exhaustive Scenarios):

Book: Test SUB_BOOK, PART, BAB, CHAPTER, MASALAH, SECTION. Assert: Structure H(x) Node + Tree Update + API Call.
Manuscript: Inherited: Test PART..CHAPTER (as above). Native: Test FOLIO, PAGE, SECTION. Assert: Marker H4 (for Folio/Page) + Tree Update + API Call.
Audio: Test SEGMENT, TRACK, MARKER. Assert: Marker H4/H5 + Player Timeline Update + API Call.
Video: Test SEGMENT, SCENE, SHOT. Assert: Marker H4/H5 + Player Timeline Update + API Call.
Implement Logic (useStudioContentProcess.js): insertNode(type, title, time):

Optimistic UI: Immediately call editorStore.insertNode(...) + mediaStore.addSegment(...).
Persistence (Rule V.4 & V.5): "المنطق ينتمي للخدمات (Services). الكنترولرز هي لتوجيه المسارات (Routing/Inertia) فقط." و "إصلاح مخالفة واحدة في كل مرة. لا للتحسينات الشاملة (Mass-Refactors) التي قد تسبب تراجعاً." Call API nodes.store.
Rollback: If API fails, undo UI changes.
Step 3: StudioAddButton (The Smart Trigger) 🖱️
Goal Implement the UI component that triggers the Orchestrator, positioned after the Entity Title, with context awareness.

Reverse Engineering Workflow (TDD) Create Test: 
tests/Browser/Studio/StudioAddButtonTest.php
 Define Expectations (Exhaustive Interaction):

Book Context: Click Button -> Dropdown lists: SUB_BOOK..SECTION. Select CHAPTER -> Input appears -> Enter -> Orchestrator called with CHAPTER.
Manuscript Context: Click Button -> Dropdown lists: Book Types + FOLIO, PAGE. Select FOLIO -> Input default "Folio {Next}" -> Enter -> Orchestrator called with FOLIO.
Audio Context: Click Button -> Dropdown lists: SEGMENT, TRACK, MARKER. Select SEGMENT -> Input default Time {Current} -> Enter -> Orchestrator called with SEGMENT.
Video Context: Click Button -> Dropdown lists: SEGMENT, SCENE, SHOT. Select SCENE -> Input default Time {Current} -> Enter -> Orchestrator called with SCENE.
Changes Create Component: 
resources/js/Technologies/Studio/Components/StudioAddButton.vue

Props: type, contextData: Object { currentTime, currentFolio, lastMarker }
Logic: Dynamic List: Use ContentNodeType.allowedFor(type). Auto-Complete: If Audio/Video: Default time = props.contextData.currentTime. If Manuscript: Default title = "Next Folio" based on context. Action: Emit insert-node -> Call useStudioContentProcess.
Integrate in StudioLayout.vue: Location: Immediately after the Entity Title (inside the flex container). Context Binding: Calculate currentFolio from availableNodes. Bind currentTime from mediaStore.

Step 4: Editor Implementation (The Dual Path) ✍️
Goal Implement Tiptap commands to handle the two distinct types of nodes differently.

Changes [MODIFY] 
resources/js/Technologies/Editor/Core/TiptapEditor.vue

Command: insertStructureNode(type, title, level): Logic: Standard Tiptap toggleHeading({ level }). Output: <h3>Title</h3>.
Command: insertMarkerNode(type, title, time/folio): Logic: Custom insertion of a "Marker Node" (or H4 with attributes). Output: <h4 class="structure-marker" data-type="folio" data-folio="5">Title</h4>.
[MODIFY] 
resources/js/Technologies/Editor/EditorClient.vue

Listener: Receive event from Orchestrator.
Decision: If type.behavior === 'container' -> Call insertStructureNode. If type.behavior === 'marker' -> Call insertMarkerNode.
Step 5: Backend Persistence (The Validator) 🛡️
Goal Implement the standardized API to store new nodes, strictly enforcing the "Logic Ownership" (Rule V.4).

Reverse Engineering Workflow (TDD) Create Test: tests/Feature/ContentNode/ContentNodeStoreTest.php Define Expectations (Total Validation Matrix): Book, Manuscript, Audio, Video (Validation against MongoDB schemas).

Changes [NEW] app/Http/Controllers/ContentNodeController.php

Method: store(Request $request, string $type, string $slug)
Logic (Rule V.4 & V.5): "المنطق ينتمي للخدمات (Services). الكنترولرز هي لتوجيه المسارات (Routing/Inertia) فقط." و "إصلاح مخالفة واحدة في كل مرة. لا للتحسينات الشاملة (Mass-Refactors) التي قد تسبب تراجعاً."
[MODIFY] 
app/Services/EntityContentService.php

Logic: Resolve Entity, Type Guard, Schema Guard, Persistence (Create ContentNode MongoDB).
[MODIFY] 
routes/web.php

Add Route: Route::post('/studio/{type}/{slug}/nodes', [App\Http\Controllers\ContentNodeController::class, 'store'])->name('studio.nodes.store');
Step 6: Harmony Unification (The Grand Unifier) 🤝
Goal Ensure that actions initiated from the Player trigger the same Universal Orchestrator to keep the Editor updated instantly.

Changes [MODIFY] 
resources/js/Technologies/Player/PlayerClient.vue

Logic (Rule V.4 & V.2): "المنطق ينتمي للخدمات (Services). الكنترولرز هي لتوجيه المسارات (Routing/Inertia) فقط." و "يُمنع استيراد ملفات من تقنيات أخرى." Player emits 'add-node' event. Studio Layout listens and calls Orchestrator. Elimination: Remove direct axios calls.
Step 7 & 8: Master Verification (Final Gold Standard) 🛸
Step 7: HarmonicExhaustiveTest.php - Verify Book/Manuscript/Media clusters.
Step 8: SeedRealisticData.php - Update to populate full hierarchy including h4 markers.
