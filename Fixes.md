1️⃣ Hydration & Dehydration

Livewire:

Stores component state on initial render (dehydration) as a JSON snapshot in the HTML.

On interaction, state is sent back to the server (hydration) and re-applied before running actions.

After server action, new snapshot is returned with updated HTML.

Spark:
✅ We now do hydration/dehydration:

comp.dataset.snapshot stores the snapshot.

spark:model inputs merge into snapshot before sending to server.

Server applies snapshot to the functional component via $snapshot array.

Response includes updated snapshot and HTML.

Missing / could improve:

Checksum / tamper-proofing — right now, a user can manually edit the snapshot in the browser and break state. Livewire generates a hash of snapshot + component to validate on server.

Nested component snapshot handling — if a component contains another Spark component, the snapshot of children isn’t fully tracked yet.

2️⃣ Actions / Event System

Livewire:

wire:click="action" triggers server-side method.

Payload can be passed (wire:click="toggle(1)").

Supports global events ($emit, $on) for cross-component communication.

Spark:
✅ We have:

action(name, callback) and call_action(name, payload).

Emitting events: emit(name, payload) (placeholder) — currently not fully implemented for global handling.

Missing / could improve:

Event bubbling between components ($emit + $on) for inter-component communication.

Named payload support like toggle(1) — right now we handle data-id only.

3️⃣ Two-way binding

Livewire:

wire:model="property" automatically syncs input changes to backend snapshot.

Spark:
✅ We now have:

spark:model inputs merge into snapshot before sending to server.

Works for forms / todo input.

Missing / could improve:

Real-time update (like debounce or lazy) to prevent sending every keystroke.

Nested arrays in model (e.g., todos[0].title) aren’t fully supported yet.

4️⃣ DOM diffing & partial update

Livewire:

Only updates parts of the DOM that changed.

Uses keys / IDs to prevent full re-render.

Spark:
✅ Currently:

Replaces innerHTML of component (comp.innerHTML = res.html) — full component replacement.

Missing / could improve:

True DOM diffing to update only changed nodes (React-style).

Keys to preserve input focus / cursor positions.

Handling nested components without destroying children.

5️⃣ Persistence

Livewire:

Session is optional — component data resets if not persisted.

Spark:
✅ Works similarly:

Functional components store snapshot only in the DOM + server exchange.

Persistent storage must come from DB (like Todo).

Missing / could improve:

Built-in local storage / session fallback.

Automatic DB persistence for components with a “persistent” flag.

6️⃣ SPA Navigation

Livewire:

No native SPA support; page reloads are normal.

Livewire + Alpine or Turbolinks can make it SPA-like.

Spark:
✅ Now supports:

spark:navigate links.

Front/back via popstate.

Fetches partial HTML into #app.

Missing / could improve:

Loading indicators / skeletons.

Prefetching pages/components.

Scroll position management.

7️⃣ Security

Livewire:

Snapshot protected via checksum, prevents client tampering.

Spark:
❌ Missing:

Snapshot can be modified in browser and sent back.

Could add a simple hash of component name + snapshot + secret key to validate on server.

8️⃣ Nested / reusable components

Livewire:

<livewire:counter> or <x-slot> allows nesting.

Props / state can be passed to child components.

Spark:
✅ Currently supports:

<div data-spark="Counter" ...> functional call.

Props passed via second parameter (snapshot/props).

Missing / could improve:

JSX-style <Counter /> or <Todo /> syntax fully parsed in PHP.

Nested snapshot merging (parent + child).

Summary Table (Spark vs Livewire)
Feature	Spark	Livewire	Notes / Missing
Hydration/Dehydration	✅ snapshot stored in DOM	✅ snapshot + checksum	Spark missing checksum/tamper-proofing
Actions	✅ action(name, callback)	✅ wire:click="method"	Spark missing payload parsing flexibility, global events
Two-way binding	✅ spark:model	✅ wire:model	Spark missing debounce/lazy
DOM diffing	❌ full innerHTML replace	✅ minimal updates	Could implement a diff/patch engine
Persistence	✅ via DB / snapshot	✅ session optional	Spark snapshot resets if page reloads
SPA navigation	✅ spark:navigate	❌	Basic handled, could add prefetch, scroll
Nested components	✅ functional	✅ blade/livewire	Nested snapshot merging not yet
Security	❌	✅ checksum	Important for production
Input forms	✅	✅	Works after merging spark:model
TL;DR: What we are missing compared to Livewire

Snapshot tamper-proofing (checksum)

Partial DOM diffing / smart re-rendering

Nested component snapshot merging

Advanced action payloads / method args

Debounce/lazy for input updates

Optional persistence in browser (local/session)

Event bubbling / cross-component emit-on server

Everything else we basically have in a lightweight, functional/procedural way, which is exactly what you wanted.

Spark v2 Roadmap (Functional / Procedural)
📦 Core Goals

Keep procedural + functional style (no mandatory classes).

Support reactive components with hydration/dehydration (like Livewire).

Support partial DOM updates (diffing) without full replacement.

Integrate lightweight SPA navigation.

Support two-way binding (spark:model) for forms.

Provide persistent data via DB, optional local snapshot.

Be secure against client snapshot tampering.

1️⃣ Component State & Snapshot

Current:

Snapshot stored in data-spark-snapshot in JSON.

Merged from spark:model inputs before sending.

Next Steps:

Add checksum validation:

$hash = hash_hmac('sha256', json_encode($snapshot), SPARK_SECRET);


Send $hash with request. Reject invalid hashes.

Support nested snapshots for child components.

Store component props separately from state for better re-render control.

API (functional):

return function($compId, $snapshot=[], $props=[]) {
    $count = &$snapshot['count'] ?? 0;

    action('inc', fn()=> $count++);
    render(fn()=> include 'view.php');
};

2️⃣ Actions & Events

Current:

action(name, callback)

call_action(name, payload)

Next Steps:

Support method payloads (action('toggle', fn($id)=>...)).

Support global events: emit('eventName', $payload) / on('eventName', fn($payload)=>...).

Support component-to-component communication using component IDs or tags.

Example:

action('add', fn($payload)=> addTodo($payload['task']));
emit('todo.updated', ['count'=>count_todos()]);

3️⃣ DOM Diffing & Partial Update

Current:

Replaces innerHTML of the component.

Next Steps:

Implement a lightweight DOM diffing engine in JS:

Compare old vs new HTML using data-spark-id as key.

Replace only changed nodes.

Preserve input focus & cursor position.

Use data-key for array items (todos) to avoid full re-render.

Benefits:

Todo list can update one task without destroying other inputs.

Prevents flicker on forms or counters.

4️⃣ Two-Way Binding

Current:

spark:model inputs merged into snapshot before request.

Next Steps:

Support nested objects: spark:model="todos[0].title".

Optional debounce/lazy update:

<input spark:model.lazy="newTask">
<input spark:model.debounce.300ms="newTask">

5️⃣ SPA Navigation

Current:

spark:navigate with popstate.

Next Steps:

Add prefetching: fetch target page snapshot & HTML on hover.

Add scroll restoration for back/forward.

Add transition hooks: onNavigateStart, onNavigateEnd.

Support optional component mounting on SPA routes.

6️⃣ Persistence

Current:

Snapshot in DOM, DB optional.

Next Steps:

Optional browser storage (localStorage/sessionStorage) for non-persistent components:

localStorage.setItem(compId, JSON.stringify(snapshot));


Auto-hydrate snapshot from DB for persistent components.

Provide functional helpers for DB CRUD operations (like Next.js actions).

7️⃣ Nested Components / Props

Current:

Functional component call via spark_component('Counter').

Next Steps:

Support nested components: spark_component('Todo', snapshot, props).

Merge child snapshot into parent snapshot.

Optional JSX-like syntax in PHP: <Counter count=1 /> → parsed into PHP call.

8️⃣ Security / Validation

Validate snapshot checksum.

Validate payload data against DB / server before updating state.

Optional user permissions for actions/events.

Fix snapshot for nested inputs (spark:model) ✅

Add SPA navigation properly (spark:navigate) ✅

Implement checksum for snapshots 🔒

Refactor actions to accept payloads ✅

Implement lightweight DOM diffing 🟡

Nested component snapshots 🟡

Optional debounce/lazy binding for inputs 🟡

Event system for global inter-component events 🟡

Optional localStorage snapshot persistence 🟡