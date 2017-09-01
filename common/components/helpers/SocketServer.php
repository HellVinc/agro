<?php

namespace common\components\helpers;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\WampServerInterface;

class SocketServer implements WampServerInterface
{
    protected $clients;

    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();


    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

//    public function onMessage(ConnectionInterface $from, $msg) {
//        $numRecv = count($this->clients) - 1;
//        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
//            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
//
//        foreach ($this->clients as $client) {
//            if ($from !== $client) {
//                // The sender is not the receiver, send to each client connected
//                $client->send($msg);
//            }
//        }
//    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
        $entryData = json_decode($entry, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['category'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$entryData['category']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}