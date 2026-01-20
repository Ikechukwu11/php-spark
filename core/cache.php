<?php
function cache_remember(string $key, callable $callback, int $ttl = 10)
{
  $cacheFile = sys_get_temp_dir() . "/spark_$key.cache";

  if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
    return unserialize(file_get_contents($cacheFile));
  }

  $value = $callback();
  file_put_contents($cacheFile, serialize($value));

  return $value;
}
