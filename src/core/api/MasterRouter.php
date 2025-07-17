<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use App\core\Notifier;

/* 
===================================================================================================================================
=                                                    Convention Interne :                                                         =
=   Le fichier `MasterRouter.php` est volontairement en majuscule pour signaler son rôle de point d’entrée principal du backend.  =
=   Ce n’est pas une classe, mais sa centralité justifie ce traitement spécial.                                                   =
===================================================================================================================================
*/
use App\auth\Auth;
use App\core\doctor\DoctorRouter;
use App\core\patient\PatientRouter;
use App\core\secretary\SecretaryRouter;

header('Content-Type: application/json');

Auth::checkSession();
$user = Auth::getUserSessionInfo();
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? null;

switch ($user['role']) {
    case 'doctor':
        $response = (new DoctorRouter) -> router($action, $data);
        break;
    case 'patient':
        $response = (new PatientRouter)->router($action, $data);
        break;
    case 'secretary':
        $response = (new SecretaryRouter)->router($action, $data);
        break;
    default:
        http_response_code(403);
        $response = ['error' => 'Unauthorized role'];
}
$notifierMessages  = Notifier::flush();

$finalResponse = [
    'data' => $response,
    'notifications' => $notifierMessages,
];

echo json_encode($finalResponse);
exit();
?>