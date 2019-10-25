<?php
// adapted from https://www.php.net/manual/en/function.stream-select.php#122839

$socketTarget = $argv[1] ?? die('No Socket Target specified! (e.g. 127.0.0.1:8080)'.PHP_EOL);

$socket = stream_socket_server("tcp://$socketTarget");

if (false === $socket) {
    echo "Did not get a resource, $errNo ($errStr)";
    exit(1);
}

stream_set_blocking($socket, false);

$connections = []; // mapping from `$peer` to `$connection`
$userNames = []; // mapping from `$peer` to `$userName`
$read = [];
$write = null;
$except = null;

while (1) {
    // look for new connections
    if ($c = @stream_socket_accept($socket, empty($connections) ? -1 : 0, $peer)) {
        $userName = fread($c, 1024);
        echo $userName.' connected'.PHP_EOL;
        fwrite($c, 'Server: Hello '.$userName.PHP_EOL);
        $connections[$peer] = $c;
        $userNames[$peer] = $userName;
    }

    // wait for any stream data
    $read = $connections;
    if (stream_select($read, $write, $except, 1)) {

        foreach ($read as $c) {
            $peer = stream_socket_get_name($c, true);

            if (feof($c)) {
                echo 'Connection closed '.$peer.':'.$userNames[$peer].PHP_EOL;
                fclose($c);
                unset($connections[$peer]);
            } else {
                $contents = trim(fread($c, 1024)); // can lead to long messages being split to multiple messages
                fwrite($c, "\xE2\x9C\x94"); // ✔ ack receiving
                // for debugging
                // echo $peer.':'.$userNames[$peer].': '.trim($contents).PHP_EOL;
                foreach($connections as $connection) {
                    if ($c === $connection) { // do not send me my message
                        continue;
                    }

                    fwrite($connection, $userNames[$peer].': '.trim($contents).PHP_EOL);
                }

                fwrite($c, "\xE2\x9C\x94".PHP_EOL); // ✔ ack sending to all connected clients
            }
        }
    }
}
