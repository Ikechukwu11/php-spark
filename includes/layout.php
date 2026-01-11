<?php

function layout(callable $slot)
{ ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Spark App</title>
        <script src="/assets/spark.js" defer></script>
        <link rel="stylesheet" href="/assets/app.css">
        <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
        <link rel="manifest" href="/assets/site.webmanifest">
    </head>

    <body>

        <div id="app">
            <?php $slot(); ?>
        </div>

    </body>

    </html>
<?php }
