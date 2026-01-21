<?php
layout(
  function () {
?>
  <h1>Welcome to Spark ⚡</h1>
  <p>This is classic PHP with reactive superpowers.</p>
  <div class="comp">

    <?= lazy('Counter') ?>
    <?= lazy('Dashboard') ?>
    <?= lazy('Todo', [], ['skeletonType' => 'todo', 'skeletonCount' => 5]) ?>

  </div>
<?php
  },
  function () {
    echo spark_component('Navbar');
  }
);
