<?php
require_once __DIR__ . '/../database/models/todo.php';
return function ($compId, $snapshot = ['todos' => [], 'newTask' => '']) {

    $todos = &$snapshot['todos'];
    $newTask = &$snapshot['newTask'];
    if (!is_array($todos)) $todos = [];

    // On first render, load from DB
    if (!count($todos)) $todos = todo_all();

    // Add
    action('add', function () use (&$todos, &$newTask) {
        if (!trim($newTask)) return;
        $id = todo_create($newTask);
        $todos[] = ['id' => $id, 'title' => $newTask, 'completed' => 0];
        $newTask = '';
    });

    // Toggle
    action('toggle', function ($payload) use (&$todos) {
        $todo = todo_find($payload[0]);
        if (!$todo) return;
        $todo['completed'] = $todo['completed'] ? 0 : 1;
        todo_update($todo['id'], ['completed' => $todo['completed']]);
        foreach ($todos as &$t) if ($t['id'] == $todo['id']) $t = $todo;
    });

    // Delete
    action('delete', function ($payload) use (&$todos) {
        todo_delete($payload[0]);
        $todos = array_values(array_filter($todos, fn($t) => $t['id'] != $payload[0]));
    });

    render(function () use (&$todos, &$newTask, $compId) { ?>
        <div data-spark-id="<?= $compId ?>" class="todo-component">
            <h2>Todo Component</h2>
            <input type="text" spark:model="newTask" value="<?= htmlspecialchars($newTask ?? '') ?>" placeholder="New task" />
            <button spark:click="add">Add</button>
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
        </div>
<?php });
};
