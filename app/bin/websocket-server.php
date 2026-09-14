<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Clue\React\Redis\Factory as RedisFactory;

final class NotificationBroadcaster implements MessageComponentInterface {
	/** @var array<string, \SplObjectStorage> userId => offene Verbindungen dieses Users */
	private array $connectionsByUser = [];

	/** @var array<int, string> Connection-Resource-ID => userId, zum Aufraeumen */
	private array $userByConnection = [];

	public function onOpen(ConnectionInterface $conn): void {
		parse_str($conn->httpRequest->getUri()->getQuery(), $params);
		$userId = $params['userId'] ?? null;

		if (!$userId) {
			$conn->close();
			return;
		}

		$this->connectionsByUser[$userId] ??= new \SplObjectStorage();
		$this->connectionsByUser[$userId]->attach($conn);
		$this->userByConnection[$conn->resourceId] = $userId;
	}

	public function onClose(ConnectionInterface $conn): void {
		$userId = $this->userByConnection[$conn->resourceId] ?? null;
		if ($userId && isset($this->connectionsByUser[$userId])) {
			$this->connectionsByUser[$userId]->detach($conn);
		}
		unset($this->userByConnection[$conn->resourceId]);
	}

	public function onMessage(ConnectionInterface $from, $msg): void {
		// Der Client sendet aktuell nichts, das wir verarbeiten muessten.
	}

	public function onError(ConnectionInterface $conn, \Exception $e): void {
		$conn->close();
	}

	public function broadcastToUser(string $userId, string $payload): void {
		if (!isset($this->connectionsByUser[$userId])) {
			return;
		}
		foreach ($this->connectionsByUser[$userId] as $conn) {
			$conn->send($payload);
		}
	}
}

$loop = Loop::get();
$broadcaster = new NotificationBroadcaster();

$webSocketServer = new HttpServer(new WsServer($broadcaster));
$port = $_ENV['WEBSOCKET_PORT'] ?? 8081;
$socket = new SocketServer("0.0.0.0:$port", [], $loop);
new IoServer($webSocketServer, $socket, $loop);

(new RedisFactory($loop))->createClient('redis:6379')->then(function ($redis) use ($broadcaster) {
	$redis->subscribe('notifications');
	$redis->on('message', function ($redisChannel, $payload) use ($broadcaster) {
		$data = json_decode($payload, true);
		$broadcaster->broadcastToUser($data['recipient_id'], $payload);
	});
});

echo "WebSocket-Server laeuft auf Port $port\n";
$loop->run();