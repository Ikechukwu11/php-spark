<?php
return function ($compId, $snapshot = []) {
    render(function () use ($compId) { ?>
        <div data-spark-id="<?= $compId ?>">
            <h2>Profile Component</h2>
            <p>Name: Demo User</p>
        </div>
    <?php });
};