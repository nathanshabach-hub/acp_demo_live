<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
	$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
	$file = __DIR__ . $requestedPath;
	if ($requestedPath !== '/' && is_file($file)) {
		return false;
	}
}

use App\Application;
use Cake\Http\Server;

require dirname(__DIR__) . '/config/bootstrap.php';

$server = new Server(new Application(CONFIG));
$server->emit($server->run());
