<?php

declare(strict_types=1);

namespace App\core\patient;

//Classes:
use App\auth\Auth;
use App\auth\Csrf;
use App\auth\Pass;
use App\core\Notifier;
use App\controllers\patient\Doctor;
use App\controllers\patient\Calendar;
use App\controllers\patient\Patient;

//Traits:
use App\traits\Dto;
use App\traits\Sanitize;
use App\traits\Validator;

final class PatientRouter
{

    use Validator;
    use Sanitize;
    use Dto;


    public function router(string $action, array $data): array
    {
        Auth::requirePatientLogin();

        $sessionVerification = Auth::isPatientLogged();

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
            //Doctor:
            'getDoctorsPatient' => (new Doctor($var))->getDoctorsPatient(),

            //Partient
            'getPatientInfo' => (new Patient($var))->getPatientInfo(),
            'updatePatientInfo' => (new Patient($var))->updatePatientInfo(),
            
            //Rdv:
            'getAvailable'=> (new Calendar($var))->getAvailable(),
            'takeAppointment'=> (new Calendar($var))->takeAppointment(),

            //Csrf: 
            'getValidToken' => (new Csrf($var))->getValidToken(),

            //Password
            'updatePassword' => (new Pass($var))->updatePassword(),

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
