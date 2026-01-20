<?php
require_once __DIR__ . '/../../database/models/todo.php';
require_once __DIR__ . '/actions.php';

return function ($compId, $snapshot = [
  'stats' => []
]) {

  $stats = &$snapshot['stats'];

  // Initial load
  if (empty($stats)) {
    $stats = todo_stats();
  }

  // Register actions (currently empty, future-proof)
  // dashboard_actions($snapshot);

  render(function () use ($compId, $stats) { ?>
    <div data-spark-id="<?= $compId ?>" class="dashboard">

      <h2>Dashboard</h2>

      <div class="stats-grid">
        <div class="stat-card">
          <strong><?= $stats['total'] ?? 0 ?></strong>
          <span>Total Todos</span>
        </div>

        <div class="stat-card">
          <strong><?= $stats['completed'] ?? 0 ?></strong>
          <span>Completed</span>
        </div>

        <div class="stat-card">
          <strong><?= $stats['uncompleted'] ?? 0 ?></strong>
          <span>Pending</span>
        </div>
      </div>

    </div>
<?php });
};
