<?php
header('Content-Type: text/plain; charset=utf-8');

echo "Ace Drainer Health Check\n";
echo "==============================\n\n";

$requirements = [
  'PHP 7.3 or higher',
  'PHP extensions: curl, json',
  'Outbound HTTPS (port 443) access',
  'Full Proxy mode: write access to /.proxy_guard'
];

echo "Minimum Requirements:\n";
foreach ($requirements as $req) {
  echo "- {$req}\n";
}
echo "\n";

function section($title) {
  echo "------------------------------\n";
  echo $title . "\n";
  echo "------------------------------\n";
}

section('PHP Environment');
printf("PHP Version         : %s %s\n", PHP_VERSION, version_compare(PHP_VERSION, '7.3', '>=') ? '(OK)' : '(Needs >= 7.3)');
printf("curl extension      : %s\n", extension_loaded('curl') ? 'Loaded (OK)' : 'Missing');
printf("json extension      : %s\n", extension_loaded('json') ? 'Loaded (OK)' : 'Missing');
printf("open_basedir        : %s\n", ini_get('open_basedir') ?: 'Not set');
printf("allow_url_fopen     : %s\n", ini_get('allow_url_fopen'));

section('Filesystem Write Test');
$testDir = __DIR__ . '/.proxy_guard_test';
$writeOk = @mkdir($testDir, 0755, true);
if ($writeOk) {
  @file_put_contents($testDir . '/test.txt', 'ok');
  @unlink($testDir . '/test.txt');
  @rmdir($testDir);
}
echo $writeOk
  ? "Write test: OK (Full Proxy mode compatible)\n"
  : "Write test: FAILED (Use Shared Host mode or enable write access)\n";

section('Outbound HTTPS');
function testCurl($url, $verify = true) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => $verify,
    CURLOPT_SSL_VERIFYHOST => $verify ? 2 : 0,
  ]);
  $res = curl_exec($ch);
  $err = curl_error($ch);
  curl_close($ch);
  return $res !== false ? 'OK' : ('ERROR: ' . $err);
}
echo "Strict SSL   : " . testCurl('https://www.cloudflare.com/cdn-cgi/trace', true) . "\n";
echo "Relaxed SSL  : " . testCurl('https://www.cloudflare.com/cdn-cgi/trace', false) . "\n";

section('Next Steps');
echo "- Upload this file with your deployment\n";
echo "- Visit it in your browser (health-check.php) and verify all checks\n";
echo "- Remove the file after testing\n";
?>