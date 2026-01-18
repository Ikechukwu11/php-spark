<?php
require_once __DIR__ . '/../database/models/todo.php';
return function ($compId, $snapshot = [
    'todos' => [],
    'newTask' => '',
    'searchTerm' => '',
    'page' => 1,
    'meta' => []
]) {

    $todos = &$snapshot['todos'];
    $newTask = &$snapshot['newTask'];
    $page = max(1, $snapshot['page'] ?? 1); //&$snapshot['page'];
    $meta = &$snapshot['meta'];

    if (!is_array($todos)) $todos = [];

    // On first render, load from DB
    if (!count($todos)) {
        //$res = todo_paginate($page, 5);
        //$todos = $res['data'];
        //$snapshot['meta'] = $res['meta'];
        // On first render or if page snapshot changes, load paginated todos
        paginate_apply(
            $snapshot,                           // The component snapshot (passed by reference)
            fn($p, $pp) => todo_paginate($p, $pp), // Callback to fetch paginated data
            5,                                   // Items per page
            'todos',                             // Key to store data in snapshot
            'meta'                                // Key to store pagination metadata
        );
    }


    // Add
    action('add', function () use (&$todos, &$newTask, &$snapshot) {
        if (!trim($newTask)) return;
        $id = todo_create($newTask);
        $todos[] = ['id' => $id, 'title' => $newTask, 'completed' => 0];
        $newTask = '';
        state('todos_count', 'global', count($todos));
        // Refresh Navbar HTML once without extra boilerplate
        emit_refresh_once([['component' => 'Navbar', 'snapshot' => []]]);
        // Recalculate snapshot data for current page
        paginate_apply($snapshot, fn($p, $pp) => todo_paginate($p, $pp), 5, 'todos', 'meta');
    });

    // Toggle
    action('toggle', function ($payload) use (&$todos) {
        $todo = todo_find($payload);
        if (!$todo) return;
        $todo['completed'] = $todo['completed'] ? 0 : 1;
        todo_update($todo['id'], ['completed' => $todo['completed']]);
        foreach ($todos as &$t) if ($t['id'] == $todo['id']) $t = $todo;
    });

    // Delete
    action('delete', function ($payload) use (&$todos, &$snapshot) {
        $todo = todo_find($payload);
        if (!$todo) return;
        todo_delete($payload);
        $todos = array_values(array_filter($todos, fn($t) => $t['id'] != $payload));
        state('todos_count', 'global', count($todos));
        // Refresh Navbar HTML once without extra boilerplate
        emit_refresh_once([['component' => 'Navbar', 'snapshot' => []]]);
        // Recalculate snapshot data for current page
        paginate_apply($snapshot, fn($p, $pp) => todo_paginate($p, $pp), 5, 'todos', 'meta');
    });

    action('paginate', function ($page) use (&$snapshot, &$todos) {
        $snapshot['page'] = (int)$page;

        // $res = todo_paginate($snapshot['page'], 5);
        // $todos = $res['data'];
        //$snapshot['meta'] = $res['meta'];
        // Apply pagination for the new page
        paginate_apply($snapshot, fn($p, $pp) => todo_paginate($p, $pp), 5, 'todos', 'meta');
    });

    // ----------------- New: Search action -----------------
    action('search', function ($payload) use (&$snapshot) {
        $snapshot['page'] = 1; // reset page on new search
        $snapshot['searchTerm'] = $payload[0] ?? '';
        paginate_apply($snapshot, fn($p, $pp) => todo_paginate($p, $pp, 's', $snapshot['searchTerm']), 5, 'todos', 'meta');
    });


    render(function () use (&$todos, &$newTask, $compId, &$snapshot) { ?>
        <div data-spark-id="<?= $compId ?>" class="todo-component">
            <h2>Todo Component</h2>
            <input type="text" spark:model="newTask" value="<?= htmlspecialchars($newTask ?? '') ?>" placeholder="New task" />
            <button spark:click="add">Add</button>

            <input
                type="text"
                spark:model="searchTerm"
                value="<?= htmlspecialchars($snapshot['searchTerm'] ?? '') ?>"
                placeholder="Search todos..."
                spark:debounce="300"
            spark:change="search"
            />

            <?php pagination($snapshot, 'paginate'); ?>

            <ul>
                <?php foreach ($todos as $t): ?>
                    <li>
                        <div>
                            <input type="checkbox" <?= $t['completed'] ? 'checked' : '' ?> spark:click="toggle" data-id="<?= $t['id'] ?>" />
                            <?= htmlspecialchars($t['title']) ?>
                        </div>
                        <button spark:click="delete" data-id="<?= $t['id'] ?>">×</button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php pagination($snapshot); ?>
        </div>
<?php });
};
