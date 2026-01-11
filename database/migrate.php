<?php
require __DIR__ . '/../includes/db.php';

/**
 * Run all migrations in database/migrations/
 */
function run_migrations()
{
  $folder = __DIR__ . '/migrations';
  if (!is_dir($folder)) return;

  foreach (glob($folder . '/*.php') as $file) {
    echo "Running migration: " . basename($file) . "\n";
    require $file;
  }
  echo "All migrations done.\n";
}

// Run migrations when executed
run_migrations();
