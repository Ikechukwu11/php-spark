<?php

function model_all(string $table): array
{
  return db_query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
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
