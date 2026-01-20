<?php
require_once __DIR__ . '/../database/models/todo.php';

return function ($compId, $snapshot = []) {
  $notifCount = &state('todo_count', 'global', null);

  if ($notifCount === null) {
    $notifCount = count(todo_all());
  }

  render(function () use ($notifCount, $compId) { ?>
    <nav data-spark-id="<?= $compId ?>" class="navbar-component">
      <a href="/" spark:navigate.hover>Home</a> |
      <a href="/about" spark:navigate.hover>About</a> |
      <a href="/dashboard" spark:navigate.hover>Dashboard</a>
      <span style="float:right;font-weight:bold;margin-left:20px;">
        Todos: <?= $notifCount ?>
      </span>
    </nav>
<?php });
};
