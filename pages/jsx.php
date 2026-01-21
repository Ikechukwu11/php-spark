    <?php
    layout(
      function () {
    ?>
      <h1>Welcome to Spark ⚡</h1>
      <p>This is classic PHP with reactive superpowers.</p>
      <p>JSX STYLE</p>
      <div class="comp">


        <Todo lazy skeletonType="todo" skeletonCount=5 />

      </div>
    <?php
      },
      function () { ?>
      <Navbar />
    <?php }
    );