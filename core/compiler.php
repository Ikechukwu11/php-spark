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

function spark_parse_propss(string $raw): array
{
  $snapshot = [];
  $options  = [];

  if ($raw === '') {
    return ['[]', '[]'];
  }

  preg_match_all(
    '/(:?\w+)(?:=(?:"([^"]*)"|\'([^\']*)\'|(\d+)|(\$[^\s]+)))?/',
    $raw,
    $matches,
    PREG_SET_ORDER
  );

  foreach ($matches as $m) {
    $key = $m[1];

    $value =
      $m[2] ?? $m[3] ?? $m[4] ?? $m[5] ?? null;

      echo "$key = $value\n";

    // 1️⃣ RAW SNAPSHOT PROP  → :stats="$stats"
    if ($key[0] === ':') {
      $prop = substr($key, 1);
      $snapshot[] = "'$prop' => $value";
      continue;
    }

    // 2️⃣ BOOLEAN FLAG
    if ($value === null || $value === '') {
      $value = 'true';
    } elseif (!is_numeric($value) && is_string($value) && $value[0] !== '$') {
      $value = var_export($value, true);
    }


    // 3️⃣ OPTION KEYS
    if (in_array($key, [
      'lazy',
      'on',
      'skeletonType',
      'skeletonCount',
      'transition'
    ], true)) {
      $options[] = "'$key' => $value";
    } else {
      // default → snapshot
      $snapshot[] = "'$key' => $value";
    }
  }

  return [
    '[' . implode(', ', $snapshot) . ']',
    '[' . implode(', ', $options) . ']'
  ];
}


function spark_parse_propss1(string $raw): array
{
  $snapshot = [];
  $options  = [];

  if (trim($raw) === '') {
    return ['[]', '[]'];
  }

  // Match props: :expr, key=value (string/number/PHP var), boolean flags
  preg_match_all(
    '/
            (:)?                     # optional colon for raw PHP expression
            (\w+)                    # prop name
            (?:=                     # optional =
                (?:
                    "([^"]*)" |     # double-quoted string -> $3
                    \'([^\']*)\' |  # single-quoted string -> $4
                    ([+-]?\d+(?:\.\d+)?) | # number -> $5
                    (\$[^\s]+)      # PHP variable -> $6
                )
            )?
        /x',
    $raw,
    $matches,
    PREG_SET_ORDER
  );

  foreach ($matches as $m) {
    $isExpr = $m[1] === ':'; // starts with :
    $key    = $m[2];
    $value  = $m[3] ?? $m[4] ?? $m[5] ?? $m[6] ?? null;

    // RAW snapshot prop :prop="$var"
    if ($isExpr) {
      $snapshot[] = "'$key' => $value";
      continue;
    }

    // Boolean flag (no value)
    if ($value === null || $value === '') {
      $value = 'true';
    } elseif (is_string($value) && !is_numeric($value) && $value[0] !== '$') {
      // wrap string literals
      $value = var_export($value, true);
    }

    // Option keys (lazy, skeleton, transition)
    if (in_array($key, [
      'lazy',
      'on',
      'skeletonType',
      'skeletonCount',
      'transition'
    ], true)) {
      // **Numbers should stay numbers, not true**
      if (is_numeric($value)) {
        $options[] = "'$key' => $value";
      } else {
        $options[] = "'$key' => $value";
      }
    } else {
      // Everything else → snapshot
      $snapshot[] = "'$key' => $value";
    }
  }

  return [
    '[' . implode(', ', $snapshot) . ']',
    '[' . implode(', ', $options) . ']'
  ];
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