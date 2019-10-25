# POC-chat-server-PHP

Just a proof of concept of using PHP to create a simple chat server that accepts connections and broadcasts any message received, much like a "group chat"

I used basic ReactPHP libraries to abstract away code related to the event loop and focus on the socket connection and handling the actual stream.

## Usage

Server: `php server.php <bind_address>`

Client: `php client.php <bind_address> <client_name>`

To make the server available only locally, bind to `127.0.0.1` with a port preferably above `1023`

To make the server available for external connections, bind to `0.0.0.0` with a port preferable above `1023`