<?php

declare(strict_types=1);

namespace App\core\secretary;

//Classes:
use App\auth\Auth;
use App\auth\Csrf;
use App\auth\Pass;
use App\controllers\shared\Message;
use App\controllers\secretary\PatientManager;
use App\controllers\secretary\Appointment;
use App\controllers\secretary\DoctorManager;
use App\controllers\secretary\OpenHours;
use App\core\Notifier;
//Traits:
use App\traits\Dto;
use App\traits\Sanitize;
use App\traits\Validator;

final class SecretaryRouter
{

    use Validator;
    use Sanitize;
    use Dto;


    public function router(string $action, array $data): array
    {
        Auth::requireSecretaryLogin();

        $sessionVerification = Auth::isSecretaryLogged();

        if ($sessionVerification === true) {
            $user = Auth::getUserSessionInfo();
            $loginId = $user['loginId'];
            $userId = $user['userId'];
        } else {
            http_response_code(403);
            return ['error' => 'Unauthorized'];
        }

        if (!$this->checkVar($data)) {
            
        return ['error' => 'Validation échouée, merci de corriger les champs'];
    }

        $this->varSanitize($data);

        $var = $this->routerTell($action, $data, $loginId ,$userId);

        return match ($action) {
            // Password:
            'generateAccess' => (new Pass($var))->generateAccess(),
            
            // Message:
            'markIsRead' => (new Message($var))->markIsRead(),
            'sendMessageSecretary' => (new Message($var))->sendMessage(),
            'getSecretaryMessage' => (new Message($var))->getMessage(),
            'getUnreadMessage' => (new Message($var))->getUnreadMessage(),

            // Appointment:
            'getAllAppointment' => (new Appointment($var))->getAllAppointment(),
            'editAppointment' => (new Appointment($var))->editAppointment(),
            'addAppointment' => (new Appointment($var))->addAppointment(),
            'changeAppointmentStatus' => (new Appointment($var))->changeAppointmentStatus(),
            'countAppointments' => (new Appointment($var))->countAppointments(),

            //PatientManager:
            'addPatient' => (new PatientManager($var))->addPatient(),
            'getPatient' => (new PatientManager($var))->getPatient(),
            'updatePatientSecretary' => (new PatientManager($var))->updatePatientSecretary(),
            'searchPatientWithDoctor' => (new PatientManager($var))->searchPatientWithDoctor(),
            //DoctorManager:
            'getDoctors' => (new DoctorManager($var))->getDoctors(),

            //OpenHours
            'updateOpenHours' => (new OpenHours($var))->updateOpenHours(),

            //Csrf: 
            'getValidToken' => (new Csrf($var))->getValidToken(),

            default => $this->defaultNotif(),
        };
    }

    private function defaultNotif(): array
    {
        Notifier::log('Erreur dans SecretaryRouter aucun match ne correspond', 'fatal');
        Notifier::console('Erreur RTE survenue');
        return [];
    }
}
