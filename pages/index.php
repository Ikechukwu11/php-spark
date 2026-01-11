<style>
  .btn-counter {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    color: #fff;
  }

  .btn-counter:hover {
    background-color: #eee;
  }

  .btn-counter:first-of-type {
    background-color: #cf0909ff
  }

  .btn-counter:last-of-type {
    background-color: #09cf09ff
  }

  .comp {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .todo-component {
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
  }

  .todo-component input {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    margin-bottom: 10px;
  }

  .todo-component button {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    background-color: #0a559bff;
    color: #fff;
  }
</style>
<h1>Welcome to Spark ⚡</h1>
<p>This is classic PHP with reactive superpowers.</p>
<div class="comp">
  <?= spark_component('Counter') ?>
  <?= spark_component('Todo') ?>
</div>

<p>
  <a href="/about" spark:navigate>About</a> |
  <a href="/dashboard" spark:navigate>Dashboard</a>
</p>