<?php

namespace console\controllers;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use common\components\helpers\SocketServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\ZMQ\Context; //не забудьте поменять, если отличается

class SocketController extends \yii\console\Controller
{

//    public function actionPushSocket($port = 8080)
//    {
//        $server = IoServer::factory(
//            new HttpServer(
//                new WsServer(
//                    new SocketServer()
//                )
//            ),
//            $port
//        );
//        $server->run();
//    }

    public function actionStartSocket($port=8080)
    {
        set_time_limit(0);
        ignore_user_abort(true);
        $loop   = Factory::create();
        $pusher = new SocketServer();

//         Listen for the web server to make a ZeroMQ push after an ajax request
//        $context = new Context($loop);
//        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
//        $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
//        $pull->on('message', array($pusher, 'onNewMessage'));
//
        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new Server($loop);
        $webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new IoServer(
            new HttpServer(
                new WsServer(
                    new WampServer(
                        $pusher
                    )
                )
            ),
            $webSock
        );

        $loop->run();
    }
    public function actionStopSocket()
    {
        $loop   = Factory::create();

        $webSock = new Server($loop);
        $webSock->shutdown();

        $pusher = new SocketServer();

        $webServer = new IoServer(
            new HttpServer(
                new WsServer(
                    new WampServer(
                        $pusher
                    )
                )
            ),
            $webSock
        );

        $loop->stop();
    }
}