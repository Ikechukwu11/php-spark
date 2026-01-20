<?php
layout(
  function () {
?>
  <h1>Welcome to Spark ⚡</h1>
  <h1>About Page</h1>
  <p>This is classic PHP with reactive superpowers.</p>
<?php
  },
  function () {
    echo spark_component('Navbar');
  }
);
