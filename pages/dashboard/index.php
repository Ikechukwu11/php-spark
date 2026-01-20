<?php
layout(
  function () {
?>
  <h1>Welcome to Spark ⚡</h1>
  <h1>Dashboard Page</h1>

  <div class="comp">
    <?= lazy('Dashboard') ?>
  </div>

<?php
  },
  function () {
    echo spark_component('Navbar');
  }
);
