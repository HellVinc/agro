<?php

namespace common\components\helpers;

use common\components\UploadModel;
use common\models\Attachment;
use Yii;
use common\models\Message;
use common\models\User;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\WampServerInterface;
use yii\filters\auth\QueryParamAuth;

class SocketServer implements WampServerInterface
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    protected $subscribedTopics = array();


    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->subscribedTopics[$topic->getId()] = $topic;
        echo "New subscription to {$topic}\n";
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
        $entryData = json_decode($entry, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['room_id'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$entryData['room_id']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
    }


    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        echo "Unsubscription from {$topic}\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $conn->close();
//        $user = User::findIdentityByAccessToken($event['auth_key']);
//
//        if ($user) {
////            print_r($user);
//            \Yii::$app->user->setIdentity($user);
//
//            $event['created_by'] = $user->id;
//
//            $model = new Message();
//            $model->load($event);
//            $model->save();
//            $image = new Attachment();
//            $image->extension = 'jpg';
//            $image->url = UploadModel::uploadBase($event['file'], $model->id, 'files/message');
//            $image->table = 'message';
//            $image->object_id = $model->id;
//            $image->save();
//
//            foreach ($this->clients as $client) {
//                $client->event($topic, $model->oneFields());
//            }
//
//            echo "New publish content to {$topic} event:\n";
//            print_r($event);
//            // In this application if clients send data it's because the user hacked around in console
//            // $conn->close();
//        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

}