<?php
// -------------------- SPARK COMPONENT --------------------
function spark_component(string $name, array $snapshot = [], array $options = []): string
{
    $lazy = normalize_lazy($options);

    // If lazy, render placeholder and defer actual component
    if ($lazy) {
        return spark_lazy_placeholder($name, $snapshot, $lazy);
    }

    $id = uniqid($name . '_');
    $file = __DIR__ . "/../components/$name.php";

    if (!file_exists($file)) {
        try {
            $file = __DIR__ . "/../components/$name/index.php";
        } catch (\Throwable $th) {
            echo json_encode(['html' => 'Component not found', 'snapshot' => [], 'events' => []]);
            return '';
        }
    }

    $component = require $file;

    ob_start();
    $component($id, $snapshot);
    run_renders();
    $html = ob_get_clean();

    $snapshotJson = htmlspecialchars(json_encode($snapshot, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES);

    return '<div data-spark="' . $name . '" data-id="' . $id . '" data-snapshot=\'' . $snapshotJson . '\'>' . $html . '</div>';
}

// -------------------- LAZY NORMALIZER --------------------
function normalize_lazy(array $options): ?array
{
    if (!isset($options['lazy'])) return null;

    // true = default lazy mode
    if ($options['lazy'] === true) {
        return ['on' => 'idle'];
    }

    // string = specify trigger
    if (is_string($options['lazy'])) {
        return ['on' => $options['lazy']];
    }

    // array = custom config
    return $options['lazy'];
}

// -------------------- LAZY PLACEHOLDER --------------------
function spark_lazy_placeholder(string $component, array $snapshot, array $lazy): string
{
    $id = uniqid('lazy_');

    $snapshotJson = htmlspecialchars(
        json_encode($snapshot, JSON_HEX_APOS | JSON_HEX_QUOT),
        ENT_QUOTES
    );

    $lazyJson = htmlspecialchars(
        json_encode($lazy, JSON_HEX_APOS | JSON_HEX_QUOT),
        ENT_QUOTES
    );

    $transition = $lazy['transition'] ?? null;
    $transitionAttr = $transition
        ? 'data-transition="' . htmlspecialchars($transition) . '"'
        : 'data-transition="slide-up"';


    // 1️⃣ Check for component-specific skeleton
    $skeletonFile = __DIR__ . "/../components/$component/skeleton.php";

    if (file_exists($skeletonFile)) {
        ob_start();
        require $skeletonFile;
        $skeletonHtml = ob_get_clean();
    } else {
        // 2️⃣ Fallback skeletons
        $type  = $lazy['skeletonType'] ?? 'default';
        $count = $lazy['skeletonCount'] ?? 1;

        $skeletonHtml = '';

        for ($i = 0; $i < $count; $i++) {
            $skeletonHtml .=
                '<div class="skeleton skeleton-' .
                htmlspecialchars($type, ENT_QUOTES) .
                '"></div>';
        }
    }

    $componentAttr = htmlspecialchars($component, ENT_QUOTES);

    return <<<HTML
<div
    data-spark="$componentAttr"
    data-id="$id"
    spark:lazy
    "$transitionAttr"
    data-component="$componentAttr"
    data-snapshot="$snapshotJson"
    data-lazy="$lazyJson"
>
<div class="spark-skeleton-layer">
$skeletonHtml
</div>
<div class="spark-content-layer"></div>
</div>
HTML;
}

// -------------------- PROCEDURAL LAZY HELPER --------------------
function lazy(string $component, array $snapshot = [], array $options = []): string
{
    if (!isset($options['on'])) {
        $options['on'] = 'idle'; // default trigger
    }

    return spark_lazy_placeholder($component, $snapshot, $options);
}
