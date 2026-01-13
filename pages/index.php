<?php
layout(
  function () {
?>
  <h1>Welcome to Spark ⚡</h1>
  <p>This is classic PHP with reactive superpowers.</p>
  <div class="comp">
    <?= spark_component('Counter') ?>
    <?= spark_component('Todo') ?>
  </div>
<?php
  },
  function () {
    echo spark_component('Navbar');
  }
);
