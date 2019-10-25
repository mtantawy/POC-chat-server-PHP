<?php

use React\EventLoop\Factory;
use React\Stream\DuplexResourceStream;

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


$loop->addReadStream(STDIN, function ($stdin) use ($loop, &$stream) {
	$message = '';
    stream_set_blocking($stdin, false);

    // Possibly can lead to OOM as it continues reading forever
    while (0 !== mb_strlen($chunk = fread($stdin, 1024))) {
    	$message .= $chunk;
    }

	$stream->write($message);
});

$loop->run();
