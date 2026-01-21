<?php
function spark_compile_components(string $html): string
{
  return preg_replace_callback(
    '/<([A-Z][A-Za-z0-9_]*)\s*([^\/>]*)\/>/',
    function ($m) {
      $name = $m[1];

      [$snapshot, $options] = spark_parse_props(trim($m[2]));

      return "<?= spark_component('$name', $snapshot, $options) ?>";
    },
    $html
  );
}

function spark_parse_props(string $raw): array
{
  $snapshot = [];
  $options  = [];

  // Match props: :expr, key=value (string/number/PHP var), boolean flags
  preg_match_all(
    '/
        (:)?                       # 1: optional colon
        (\w+)                      # 2: prop name
        (?:=                       # optional =
            (?:
                "([^"]*)" |        # 3: double-quoted string
                \'([^\']*)\' |     # 4: single-quoted string
                (-?\d+(?:\.\d+)?) | # 5: int or float (THE NUMERIC GROUP)
                (\$[^\s]+)         # 6: PHP variable
            )
        )?
        /x',
    $raw,
    $matches,
    PREG_SET_ORDER
  );

  foreach ($matches as $m) {
    $isExpr = ($m[1] === ':');
    $key    = $m[2];

    // 1. Handle Boolean Flags (No '=' or value found in groups 3-6)
    if (!isset($m[3]) && !isset($m[4]) && !isset($m[5]) && !isset($m[6])) {
      $value = 'true';
    }
    // 2. Handle Numeric values (Group 5)
    elseif (isset($m[5]) && $m[5] !== '') {
      $value = $m[5]; // Keep as number (no quotes)
    }
    // 3. Handle Variables or Quoted Strings
    else {
      // Get whichever group matched
      $valRaw = $m[3] ?? $m[4] ?? $m[6] ?? '';

      if ($isExpr || (isset($m[6]) && $m[6] !== '')) {
        $value = $valRaw; // PHP expressions/variables stay raw
      } else {
        $value = var_export($valRaw, true); // String literals get quotes
      }
    }

    // Categorize into Options or Snapshot
    if (in_array($key, ['lazy', 'on', 'skeletonType', 'skeletonCount', 'transition'], true)) {
      $options[] = "'$key' => $value";
    } else {
      $snapshot[] = "'$key' => $value";
    }
  }

  return [
    '[' . implode(', ', $snapshot) . ']',
    '[' . implode(', ', $options) . ']'
  ];
}

function spark_compile_blade(string $source): string
{
    /* 1️⃣ @php ... @endphp → <?php ... ?>*/
    $source = preg_replace('/@php(.*?)@endphp/s', '<?php$1?>', $source);

    // 2️⃣ @foreach / @endforeach → foreach loops
    $source = preg_replace('/@foreach\s*\((.*?)\)/', '<?php foreach($1): ?>', $source);
    $source = preg_replace('/@endforeach/', '<?php endforeach; ?>', $source);

    // 3️⃣ @if / @elseif / @else / @endif
    $source = preg_replace('/@if\s*\((.*?)\)/', '<?php if($1): ?>', $source);
    $source = preg_replace('/@elseif\s*\((.*?)\)/', '<?php elseif($1): ?>', $source);
    $source = preg_replace('/@else/', '<?php else: ?>', $source);
    $source = preg_replace('/@endif/', '<?php endif; ?>', $source);

    /* 4️⃣ {{ expr }} → <?= expr ?>*/
    $source = preg_replace('/\{\{\s*(.*?)\s*\}\}/', '<?= $1 ?>', $source);

    // 5️⃣ Comments: {{-- comment --}} → ignored
    $source = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

    return $source;
}
