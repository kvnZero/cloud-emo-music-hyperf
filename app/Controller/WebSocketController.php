<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\SocketIOServer\Socket;
use Hyperf\SocketIOServer\SocketIOConfig;
use Hyperf\WebSocketServer\Sender;

/**
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    public function __construct(Sender $sender, SidProviderInterface $sidProvider, ?SocketIOConfig $config = null)
    {
        parent::__construct($sender, $sidProvider, $config);
    }

    /**
     * @Event("join-room")
     * @param string $data
     */
    public function onJoinRoom(Socket $socket, $data)
    {
        $socket->join($data);
        $this->emit('client-change', count($socket->getAdapter()->clients($data)));
    }

    /**
     * @Event("query-room-count")
     * @param string $data
     */
    public function queryRoomCount(Socket $socket, $data): int
    {
        return count($socket->getAdapter()->clients($data));
    }

    /**
     * @Event("disconnect")
     */
    public function onDisconnect(Socket $socket)
    {
        $this->emit('someone-leave-room', time());
    }

    /**
     * @Event("message-send")
     */
    public function onMessageSend(Socket $socket, $data)
    {
        $this->emit('message-come', $data);
    }
}