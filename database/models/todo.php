<?php
require_once __DIR__ . '/base.php';

// List all todos
function todo_all()
{
  return model_all('todos');
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
