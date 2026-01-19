<?php
require_once __DIR__ . '/../../database/models/todo.php';
require_once __DIR__.'/./actions.php';

return function ($compId, $snapshot = [
    'todos' => [],
    'newTask' => '',
    'searchTerm' => '',
    'page' => 1,
    'meta' => []
]) {

    $todos = &$snapshot['todos'];
    $newTask = &$snapshot['newTask'];

    if (!is_array($todos)) $todos = [];

    // Modify the pagination callback to accept search term
    $paginateCallback = function ($page, $perPage) use (&$snapshot) {
        $term = $snapshot['searchTerm'] ?? '';
        return todo_paginate($page, $perPage, 's',$term); // assume model supports optional search param
    };

    // On first render, load from DB
    if (!count($todos)) {
        // On first render or if page snapshot changes, load paginated todos
        paginate_apply(
            $snapshot,                           // The component snapshot (passed by reference)
            $paginateCallback, // Callback to fetch paginated data
            5,                                   // Items per page
            'todos',                             // Key to store data in snapshot
            'meta'                                // Key to store pagination metadata
        );
    }

    // 🔥 register actions
    todo_actions($snapshot, $paginateCallback);


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
