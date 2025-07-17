<?php

declare(strict_types=1);
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\login\Login;
use App\auth\Session;
use App\core\Notifier;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];


$action = $_POST['action'] ?? null;

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$action && isset($data['action'])) {
        $action = $data['action'];
    }
}

if ($method === 'POST' && $action === 'login') {

    $tellLogin = new Login($data['login'], $data['password']);
    $response = $tellLogin->authenticate();
    $notifierMessages  = Notifier::flush();

    $finalResponse = [
        'data' => $response,
        'notifications' => $notifierMessages,
    ];
    echo json_encode($finalResponse);
    exit();
}

if ($method === 'POST' && $action === 'checkConnection') {
    session_start();
    if (isset($_SESSION['userId'], $_SESSION['role'], $_SESSION['lastName'])) {
        echo json_encode([
            'connected' => true,
            'username' => $_SESSION['lastName'],
            'role' => $_SESSION['role']
        ]);
    } else {
        echo json_encode(['connected' => false]);
    }
    exit();
}

if ($method === 'POST' && $action === 'logout') {
    session_start();
    $session = new Session($_SESSION['loginId'], $_SESSION['role']);
    $session->destroy();
    echo json_encode(['success' => true, 'message' => 'Déconnecté']);
    exit();
}

echo json_encode(['error' => 'Action inconnue ou méthode non autorisée']);
exit();
