<?php

namespace Core\Classes;

use Core\Classes\User;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class Chat implements MessageComponentInterface
{
    protected $clients;
    public    $userObj, $data;

    public function __construct()
    {
        $this->clients = new SplObjectStorage;
        $this->userObj = new User;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $query);
        if ($data = $this->userObj->getUserBySession($query['token'])) {
            $this->data = $data;
            $conn->data = $data;
            $this->clients->attach($conn);
            $this->userObj->updateConnection($conn->resourceId, $data->id);
            echo "({$data->username}) is Now Connected \n";
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf(
            'Connection %d sending message "%s" to %d other connection%s' . "\n",
            $from->resourceId,
            $msg,
            $numRecv,
            $numRecv == 1 ? '' : 's'
        );
        $data = json_decode($msg, true);
        $sendTo = $this->userObj->getUser($data['sendTo']);
        $send['sendTo'] = $sendTo->id;
        $send['by'] = $from->data->id;
        $send['image'] = $from->data->image;
        $send['username'] = $from->data->username;
        $send['name'] = $from->data->name;
        $send['type'] = $data['type'];
        $send['data'] = $data['data'];
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                if ($client->resourceId == $sendTo->connection_id || $from == $client) {
                    $client->send(json_encode($send));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
