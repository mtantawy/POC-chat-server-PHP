<?php

use React\EventLoop\Factory;
use React\Stream\DuplexResourceStream;
use React\Stream\ReadableResourceStream;

require __DIR__ . '/vendor/autoload.php';

$server = $argv[1] ?? die('No Server specified! (e.g. 127.0.0.1:8080)'.PHP_EOL);
$clientName = $argv[2] ?? die('No Client Name specified! (e.g. hackerman)'.PHP_EOL);

$resource = stream_socket_client("tcp://$server", $errNo, $errStr);

if (false === $resource) {
	echo "Did not get a resource, $errNo ($errStr)";
    exit(1);
}

$loop = Factory::create();

$stream = new DuplexResourceStream($resource, $loop);

$stream->on('data', function ($chunk) use (&$stream) {
	echo $chunk;
});

$stream->on('close', function () {
    echo '[CLOSED]' . PHP_EOL;
    die();
});

$stream->on('error', function (Exception $e) {
	echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

$stream->write($clientName);

$stdin = new ReadableResourceStream(STDIN, $loop);
$stdin->pipe($stream);

$loop->run();
