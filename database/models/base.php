<?php

function model_all(string $table, ?string $orderBy = null): array
{
  return db_query("SELECT * FROM $table ORDER BY $orderBy")->fetchAll(PDO::FETCH_ASSOC);
}

function model_find(string $table, int $id): ?array
{
  $stmt = db_query("SELECT * FROM $table WHERE id = ?", [$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

function model_create(string $table, array $data): int
{
  $columns = implode(',', array_keys($data));
  $placeholders = implode(',', array_fill(0, count($data), '?'));
  db_query("INSERT INTO $table ($columns) VALUES ($placeholders)", array_values($data));
  return db()->lastInsertId();
}

function model_update(string $table, int $id, array $data): int
{
  $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
  $params = array_values($data);
  $params[] = $id;
  db_query("UPDATE $table SET $set WHERE id = ?", $params);
  return $id;
}

function model_delete(string $table, int $id): void
{
  db_query("DELETE FROM $table WHERE id = ?", [$id]);
}

function model_select(string $table, string $where = '1', array $params = []): array
{
  return db_query("SELECT * FROM $table WHERE $where", $params)->fetchAll(PDO::FETCH_ASSOC);
}

function model_count(string $table, string $where = '1', array $params = []): int
{
  $stmt = db_query(
    "SELECT COUNT(*) AS count FROM $table WHERE $where",
    $params
  );

  return (int) $stmt->fetchColumn();
}

function model_count_where(string $table, array $conditions): int
{
  $where = [];
  $params = [];

  foreach ($conditions as $col => $val) {
    $where[] = "$col = ?";
    $params[] = $val;
  }

  return model_count(
    $table,
    implode(' AND ', $where),
    $params
  );
}

function model_exists(string $table, string $where = '1', array $params = []): bool
{
  $stmt = db_query(
    "SELECT 1 FROM $table WHERE $where LIMIT 1",
    $params
  );

  return (bool) $stmt->fetchColumn();
}

function model_sum(
  string $table,
  string $column,
  string $where = '1',
  array $params = []
): float {
  $stmt = db_query(
    "SELECT COALESCE(SUM($column), 0) FROM $table WHERE $where",
    $params
  );

  return (float) $stmt->fetchColumn();
}
function model_avg(
  string $table,
  string $column,
  string $where = '1',
  array $params = []
): float {
  $stmt = db_query(
    "SELECT COALESCE(AVG($column), 0) FROM $table WHERE $where",
    $params
  );

  return (float) $stmt->fetchColumn();
}

function model_max(
  string $table,
  string $column,
  string $where = '1',
  array $params = []
): float {
  return (float) db_query(
    "SELECT COALESCE(MAX($column), 0) FROM $table WHERE $where",
    $params
  )->fetchColumn();
}

function model_min(
  string $table,
  string $column,
  string $where = '1',
  array $params = []
): float {
  return (float) db_query(
    "SELECT COALESCE(MIN($column), 0) FROM $table WHERE $where",
    $params
  )->fetchColumn();
}



function model_pluck(
  string $table,
  string $column,
  string $where = '1',
  array $params = []
): array {
  $stmt = db_query(
    "SELECT $column FROM $table WHERE $where",
    $params
  );

  return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), $column);
}



function model_paginate(string $table, ?string $where = '1', int $page = 1, int $perPage = 10): array
{
  $page = max(1, $page);
  $offset = ($page - 1) * $perPage;
  $total = db_query("SELECT COUNT(*) FROM $table WHERE $where")->fetchColumn();
  $rows = db_query("SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT $perPage OFFSET $offset")
    ->fetchAll(PDO::FETCH_ASSOC);

  return [
    'data' => $rows,
    'meta' => [
      'page' => $page,
      'perPage' => $perPage,
      'total' => (int)$total,
      'pages' => (int)ceil($total / $perPage),
    ]
  ];
}

/**
 * Paginate a table with optional dynamic search
 *
 * @param string $table Table name
 * @param int $page Current page
 * @param int $perPage Rows per page
 * @param string|null $searchTerm Optional search string
 * @param array $searchColumns Columns to search (if empty, ignores search)
 * @return array ['data' => [...], 'meta' => [...]]
 */
function model_paginate_search(
  string $table,
  int $page = 1,
  int $perPage = 10,
  ?string $searchTerm = null,
  array $searchColumns = []
): array {
  $page = max(1, $page);
  $offset = ($page - 1) * $perPage;

  $params = [];
  $where = '1'; // default no filter

  // If searchTerm and columns provided, dynamically build WHERE
  if ($searchTerm && count($searchColumns)) {
    $like = '%' . $searchTerm . '%';
    $clauses = [];
    foreach ($searchColumns as $col) {
      $clauses[] = "$col LIKE ?";
      $params[] = $like;
    }
    $where = '(' . implode(' OR ', $clauses) . ')';
  }

  // Count total rows for pagination
  $stmt = db_query("SELECT COUNT(*) FROM $table WHERE $where", $params);
  $total = (int)$stmt->fetchColumn();

  // Fetch paginated rows
  $stmt = db_query(
    "SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
  );
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  return [
    'data' => $rows,
    'meta' => [
      'page' => $page,
      'perPage' => $perPage,
      'total' => $total,
      'pages' => (int)ceil($total / $perPage),
      'searchTerm' => $searchTerm,
      'searchColumns' => $searchColumns
    ]
  ];
}
