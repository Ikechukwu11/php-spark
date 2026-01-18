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

function todo_paginate($page = 1, $perPage = 10, $search='', $where = '1')
{
  switch ($search) {
    case 'search':
    case 's':
    case 'searchTerm':
      $where = "WHERE title LIKE '%$search%'";
      break;

    default:
      $where = "WHERE 1";
      break;
  }
  return model_paginate('todos', '1', $page, $perPage);
}
