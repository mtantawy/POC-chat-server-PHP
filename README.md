# POC-chat-server-PHP

Just a proof of concept of using PHP to create a simple chat server that accepts connections and broadcasts any message received, much like a "group chat"

I used basic ReactPHP libraries to abstract away code related to the event loop and focus on the socket connection and handling the actual stream.