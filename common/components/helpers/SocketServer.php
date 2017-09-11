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
        $user = User::findIdentityByAccessToken($event['auth_key']);

        if ($user) {
//            print_r($user);
            \Yii::$app->user->setIdentity($user);

            $event['created_by'] = $user->id;

            $model = new Message();
            $model->load($event);
            $model->save();
            $image = new Attachment();
            $image->extension = 'jpg';
            $image->url = UploadModel::uploadBase($event['file'], $model->id, 'files/message');
            $image->table = 'message';
            $image->object_id = $model->id;
            $image->save();



            foreach ($this->clients as $client) {
                $client->event($topic, $model->oneFields());
            }

            echo "New publish content to {$topic} event: '{$event}''\n";
            // In this application if clients send data it's because the user hacked around in console
            // $conn->close();
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

}