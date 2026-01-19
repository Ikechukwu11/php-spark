<?php

function todo_actions(array &$snapshot, callable $paginateCallback)
{
  $todos   = &$snapshot['todos'];
  $newTask = &$snapshot['newTask'];

  // Add
  action('add', function () use (&$todos, &$newTask, &$snapshot, $paginateCallback) {
    if (!trim($newTask)) return;

    $id = todo_create($newTask);
    $todos[] = ['id' => $id, 'title' => $newTask, 'completed' => 0];
    $newTask = '';

    state('todos_count', 'global', count($todos));
    emit_refresh_once([['component' => 'Navbar', 'snapshot' => []]]);

    paginate_apply($snapshot, $paginateCallback, 5, 'todos', 'meta');
  });

  // Toggle
  action('toggle', function ($payload) use (&$todos) {
    $todo = todo_find($payload);
    if (!$todo) return;

    $todo['completed'] = $todo['completed'] ? 0 : 1;
    todo_update($todo['id'], ['completed' => $todo['completed']]);

    foreach ($todos as &$t) {
      if ($t['id'] == $todo['id']) $t = $todo;
    }
  });

  // Delete
  action('delete', function ($payload) use (&$todos, &$snapshot, $paginateCallback) {
    $todo = todo_find($payload);
    if (!$todo) return;

    todo_delete($payload);
    $todos = array_values(array_filter($todos, fn($t) => $t['id'] != $payload));

    state('todos_count', 'global', count($todos));
    emit_refresh_once([['component' => 'Navbar', 'snapshot' => []]]);

    paginate_apply($snapshot, $paginateCallback, 5, 'todos', 'meta');
  });

  // Paginate
  action('paginate', function ($page) use (&$snapshot, $paginateCallback) {
    $snapshot['page'] = (int) $page;
    paginate_apply($snapshot, $paginateCallback, 5, 'todos', 'meta');
  });

  // Search
  action('search', function ($payload) use (&$snapshot, $paginateCallback) {
    $snapshot['page'] = 1;
    $snapshot['searchTerm'] = $payload ?? '';
    paginate_apply($snapshot, $paginateCallback, 5, 'todos', 'meta');
  });
}
