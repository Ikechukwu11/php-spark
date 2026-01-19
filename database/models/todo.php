<?php
require_once __DIR__ . '/base.php';

// List all todos
function todo_all()
{
  return model_all('todos', 'id DESC');
}

// Create a todo
function todo_create($title)
{
  return model_create('todos', ['title' => $title, 'completed' => 0]);
}

// Update a todo
function todo_update($id, $data)
{
  return model_update('todos', $id, $data);
}

// Delete a todo
function todo_delete($id)
{
  return model_delete('todos', $id);
}

// Find a single todo
function todo_find($id)
{
  return model_find('todos', $id);
}

function todo_paginate($page = 1, $perPage = 10, $searchKey = '', $searchTerm = '')
{
  $where = '1';

  if (in_array($searchKey, ['search', 's', 'searchTerm'], true) && $searchTerm !== '') {
    // basic escaping (better: prepared statements in model_paginate)
    $searchTerm = addslashes($searchTerm);
    $where = "title LIKE '%{$searchTerm}%'";
  }

  return model_paginate('todos', $where, $page, $perPage);
}
