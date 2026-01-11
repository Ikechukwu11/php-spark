<?php
return function ($compId, &$snapshot = ['count' => 0]) {
    if (!isset($snapshot['count'])) $snapshot['count'] = 0;
    $count = &$snapshot['count'];

    action('inc', function () use (&$count) {
        $count++;
        emit('counter.updated', $count);
    });
    action('dec', function () use (&$count) {
        if ($count > 0)
        $count--;
        emit('counter.updated', $count);
    });

    render(function () use (&$count, $compId) { ?>
        <div data-spark-id="<?= $compId ?>" class="counter" style="text-align:center; padding:20px; border:1px solid #ccc; border-radius:8px;">
            <h2>Counter Component</h2>
            <h1><?= $count ?></h1>
            <button class="btn-counter" spark:click="dec">−</button>
            <button class="btn-counter" spark:click="inc">+</button>
        </div>
<?php });
};
