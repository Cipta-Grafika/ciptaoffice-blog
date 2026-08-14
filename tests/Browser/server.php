<?php

use Symfony\Component\Process\Process;

require __DIR__.'/prepare.php';

$projectRoot = dirname(__DIR__, 2);
$server = new Process([
    PHP_BINARY,
    'artisan',
    'serve',
    '--host=127.0.0.1',
    '--port=8011',
], $projectRoot, null, null, null);
$server->setTimeout(null);

$exitCode = $server->run(function (string $type, string $output): void {
    fwrite($type === Process::ERR ? STDERR : STDOUT, $output);
});

exit($exitCode);
