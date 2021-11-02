<?php

namespace App\Http\Controllers\WebSocket;

use App\Http\Controllers\Controller;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use OpenTok\OpenTok;
use OpenTok\MediaMode;
use OpenTok\ArchiveMode;

class BasicSocketController extends Controller implements MessageComponentInterface
{

    private $connections = [];

    private $requestQueue = [];

    private $apiKey = '46095642';
    private $apiSecret = '0ea199c51cabcd27e63585421579c1c197b60aff';

    private $opentok;


    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        var_dump($conn->resourceId);
        $this->connections[$conn->resourceId] = compact('conn') + ['email' => null];
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        $this->requestQueue = $this->removeConnection($this->requestQueue, $conn);
        $this->connections = $this->removeConnection($this->connections, $conn);

        var_dump('connections: '.count($this->connections));

        $disconnectedId = $conn->resourceId;
        foreach ($this->connections as  $resourceId => $connection) {
            $connection['conn']->send(json_encode([
                'role' => 'both',
                'type' => 'disconnect',
                'user' => $disconnectedId,
                'from_user_id' => 'server control',
                'from_resource_id' => null
            ]));
        }

    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $userId = $this->connections[$conn->resourceId]['user_id'];
        echo "An error has occurred with user $userId: {$e->getMessage()}\n";
        unset($this->connections[$conn->resourceId]);
        $conn->close();
    }

    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $conn The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $conn, $msg)
    {
        $msg = json_decode($msg);

        if (isset($msg->role)) {
            if ($msg->role === 'video') {
                $this->OnVideoMessage($conn, $msg);
            } else if ($msg->role === 'chat') {
                $this->OnChatMessage($conn, $msg);
            } else if ($msg->role === 'other') {
                $this->OnOtherMessage($conn, $msg);
            }
        }


//        if(is_null($this->connections[$conn->resourceId]['user_id'])){
//            $this->connections[$conn->resourceId]['user_id'] = $msg;
//            $onlineUsers = [];
//            foreach($this->connections as $resourceId => &$connection){
//                $connection['conn']->send(json_encode([$conn->resourceId => $msg]));
//                if($conn->resourceId != $resourceId)
//                    $onlineUsers[$resourceId] = $connection['user_id'];
//            }
//            $conn->send(json_encode(['online_users' => $onlineUsers]));
//        } else{
//            $fromUserId = $this->connections[$conn->resourceId]['user_id'];
//            $msg = json_decode($msg, true);
//            $this->connections[$msg['to']]['conn']->send(json_encode([
//                'msg' => $msg['content'],
//                'from_user_id' => $fromUserId,
//                'from_resource_id' => $conn->resourceId
//            ]));
//        }
    }

    function OnVideoMessage(ConnectionInterface $conn, $msg) {

        if (isset($msg->type)) {
            $type = $msg->type;
            if ($type === 'videoChatRequest') {
                if (count($this->requestQueue) == 0) {
                    $this->requestQueue[$conn->resourceId] = compact('conn') + ['gender' => $msg->gender] + ['name' => $msg->name] + ['email' => $msg->email];
                    $conn->send(json_encode([
                        'role' => 'video',
                        'type' => 'wait',
                    ]));
                } else {
                    foreach ($this->requestQueue as $resourceId => $connection) {
                        if ($connection['gender'] != $msg->gender) {
                            $session = $this->getSession();

                            var_dump('success');
                            $conn->send(json_encode([
                                'role' => 'video',
                                'type' => 'connected',
                                'partner' => $resourceId,
                                'sessionId' => $session['sessionId'],
                                'token' => $session['token'],
                                'name' => $connection['name'],
                                'email' => $connection['email']

                            ]));
                            $connection['conn']->send(json_encode([
                                'role' => 'video',
                                'type' => 'connected',
                                'partner' => $conn->resourceId,
                                'sessionId' => $session['sessionId'],
                                'token' => $session['token'],
                                'name' => $msg->name,
                                'email' => $msg->email
                            ]));
                            unset($this->requestQueue[$resourceId]);
                            return;
                        }
                    }

                    $this->requestQueue[$conn->resourceId] = compact('conn') + ['gender' => $msg->gender] + ['name' => $msg->name] + ['email' => $msg->email];
                    $conn->send(json_encode([
                        'role' => 'video',
                        'type' => 'wait',
                    ]));
                }
            }
            else if($type == 'message') {
                $this->sendVideoMessage($msg);
            } else if($type == 'endChat') {
                $this->endVideoChat($msg->to);
            } else if ($type == 'stopSearching') {
                if (isset($this->requestQueue[$conn->resourceId])) {
                    unset($this->requestQueue[$conn->resourceId]);
                }
            }
        }
    }

    function OnChatMessage(ConnectionInterface $conn, $msg) {
        if (isset($msg->type)) {
            $type = $msg->type;

            if($type == 'message') {
                foreach ($this->connections as  $resourceId => $connection) {
                    if ($connection['email'] == $msg->to) {
                        $connection['conn']->send(json_encode([
                            'role' => 'chat',
                            'type' => 'message',
                            'message' => $msg->message,
                            'from' => $msg->from,
                            'to' => $msg->to,
                        ]));

                        var_dump('sent');

                        return;
                    }
                }
            }

        }
    }

    function OnOtherMessage(ConnectionInterface $conn, $msg) {
        if (isset($msg->type)) {
            $type = $msg->type;
            if ($type == 'login') {
                $this->connections[$conn->resourceId]['email'] = $msg->email;
                return;
            } else if ($type === 'logout') {
                $this->connections[$conn->resourceId]['email'] = null;
                if (isset($this->requestQueue[$conn->resourceId])) {
                    unset($this->requestQueue[$conn->resourceId]);
                }
                return;
            }
        }
    }

    function removeConnection($connections, ConnectionInterface $conn)
    {
        $disconnectedId = $conn->resourceId;
        if (isset($connections[$disconnectedId])) {
            unset($connections[$disconnectedId]);
        }
        return $connections;
    }

    function getSession()
    {

        var_dump('get session');
        $this->opentok = new OpenTok($this->apiKey, $this->apiSecret);
        $sessionOptions = array(
            'archiveMode' => ArchiveMode::ALWAYS,
            'mediaMode' => MediaMode::ROUTED
        );
        $session = $this->opentok->createSession($sessionOptions);
        $sessionId = $session->getSessionId();
        $token = $session->generateToken();

        return array('sessionId' => $sessionId, 'token' => $token);

    }

    function sendVideoMessage($data) {
        var_dump($data->to);
        foreach ($this->connections as  $resourceId => $connection) {
            if ($resourceId == $data->to) {
                $connection['conn']->send(json_encode([
                    'role' => 'video',
                    'type' => 'message',
                    'message' => $data->message
                ]));

                var_dump('sent');
            }
        }
    }

    function endVideoChat($to) {
        foreach ($this->connections as  $resourceId => $connection) {
            if ($resourceId == $to) {
                $connection['conn']->send(json_encode([
                    'role' => 'video',
                    'type' => 'endChat',
                ]));
            }
        }
    }


}