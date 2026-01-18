<?php
function e($str)
{
  return htmlspecialchars($str);
}


if (!function_exists('dd')) {
  function dd(...$args)
  {
    $styles = [
      'pre' => 'background:#1e1e1e;color:#ecf0f1;padding:16px;border-radius:8px;font-size:14px;line-height:1.5;font-family:Menlo,Consolas,monospace;',
      'string' => 'color:#2ecc71;',
      'integer' => 'color:#3498db;',
      'double' => 'color:#9b59b6;',
      'boolean' => 'color:#e67e22;',
      'NULL' => 'color:#95a5a6;',
      'array' => 'color:#f1c40f;',
      'object' => 'color:#e74c3c;',
      'resource' => 'color:#1abc9c;',
      'default' => 'color:#bdc3c7;',
    ];

    $output = "<pre style=\"{$styles['pre']}\">";

    foreach ($args as $i => $arg) {
      $output .= "\n<span style=\"color:#e74c3c;\">--- [dd #" . ($i + 1) . "] ---</span>\n";
      $output .= parse_colored_var($arg, $styles) . "\n";
    }

    $output .= '</pre>';

    if (function_exists('wp_die')) {
      wp_die($output); // Proper WordPress termination
    } else {
      die($output);
    }
  }

  function parse_colored_var($var, $styles, $depth = 0)
  {
    $indent = str_repeat('  ', $depth);
    switch (gettype($var)) {
      case 'string':
        return "<span style=\"{$styles['string']}\">\"$var\"</span>";
      case 'integer':
        return "<span style=\"{$styles['integer']}\">$var</span>";
      case 'double':
        return "<span style=\"{$styles['double']}\">$var</span>";
      case 'boolean':
        return "<span style=\"{$styles['boolean']}\">" . ($var ? 'true' : 'false') . "</span>";
      case 'NULL':
        return "<span style=\"{$styles['NULL']}\">null</span>";
      case 'array':
        $result = "<span style=\"{$styles['array']}\">array</span> (\n";
        foreach ($var as $key => $value) {
          $result .= $indent . "  [$key] => " . parse_colored_var($value, $styles, $depth + 1) . "\n";
        }
        return $result . $indent . ")";
      case 'object':
        $class = get_class($var);
        $result = "<span style=\"{$styles['object']}\">object($class)</span> (\n";
        foreach ((array) $var as $key => $value) {
          $result .= $indent . "  [$key] => " . parse_colored_var($value, $styles, $depth + 1) . "\n";
        }
        return $result . $indent . ")";
      case 'resource':
        return "<span style=\"{$styles['resource']}\">resource</span>";
      default:
        return "<span style=\"{$styles['default']}\">" . htmlspecialchars(print_r($var, true)) . "</span>";
    }
  }
}


function paginate_apply(
  array &$snapshot,
  callable $query,
  int $perPage = 10,
  string $dataKey = 'data',
  string $metaKey = 'meta'
) {
  $page = max(1, (int)($snapshot['page'] ?? 1));

  $res = $query($page, $perPage);

  $snapshot[$dataKey] = $res['data'];
  $snapshot[$metaKey] = $res['meta'];
  $snapshot['page']   = $page;
}

function pagination(array &$snapshot, string $action = 'paginate')
{
  if (($snapshot['meta']['pages'] ?? 1) <= 1) return;

  $p = $snapshot['meta']['page'];
  $pages = $snapshot['meta']['pages'];

  $start = max(1, $p - 2);
  $end   = min($pages, $p + 2);
?>
  <div class="pagination" data-spark-key="pagination">

    <!-- First -->
    <button
      <?= $p <= 1 ? 'disabled' : '' ?>
      <?= $p <= 1 ? '' : 'spark:click="' . $action . '" data-page="1"' ?>>« </button>

    <!-- Prev -->
    <button
      <?= $p <= 1 ? 'disabled' : '' ?>
      <?= $p <= 1 ? '' : 'spark:click="' . $action . '" data-page="' . ($p - 1) . '"' ?>>‹ </button>

    <!-- Page numbers -->
    <?php for ($i = $start; $i <= $end; $i++): ?>
      <?php if ($i == $p): ?>
        <button class="current" data-active-page><?= $i ?></button>
      <?php else: ?>
        <button spark:click="<?= $action ?>" data-page="<?= $i ?>"><?= $i ?></button>
      <?php endif; ?>
    <?php endfor; ?>

    <!-- Next -->
    <button
      <?= $p >= $pages ? 'disabled' : '' ?>
      <?= $p >= $pages ? '' : 'spark:click="' . $action . '" data-page="' . ($p + 1) . '"' ?>> ›</button>

    <!-- Last -->
    <button
      <?= $p >= $pages ? 'disabled' : '' ?>
      <?= $p >= $pages ? '' : 'spark:click="' . $action . '" data-page="' . $pages . '"' ?>> »</button>

  </div>
<?php
}
