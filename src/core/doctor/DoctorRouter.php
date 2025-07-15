<?php

declare(strict_types=1);

namespace App\core\doctor;

//Classes:
use App\auth\Csrf;
use App\controllers\doctor\PatientManager;
use App\controllers\doctor\Consultations;
use App\controllers\doctor\PatientGet;
use App\controllers\doctor\Statistics;
use App\controllers\doctor\Credit;
use App\controllers\doctor\Appointment;
use App\controllers\shared\Message;
use App\auth\Pass;
use App\auth\Auth;
use App\core\Notifier;

//Traits
use App\traits\Dto;
use App\traits\Sanitize;
use App\traits\Validator;

final class DoctorRouter {

    use Validator;
    use Sanitize;
    use Dto;

    public function router(string $action, array $data): array
    {

        Auth::requireDoctorLogin();

        $sessionVerification = Auth::isDoctorLogged();

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
            //Password:
            'generateAccess' => (new Pass($var))->generateAccess(),
            //Message:
            'deleteMessageForReceiver' => (new Message($var))->deleteMessageForReceiver(),
            'deleteMessageForSender' => (new Message($var))->deleteMessageForSender(),
            'getSenderMessages' => (new Message($var))->getSenderMessages(),
            'markIsRead' => (new Message($var))->markIsRead(),
            'sendMessageToSecretary' => (new Message($var))->sendMessage(),
            'getMessage' => (new Message($var))->getMessage(),
            //Appointment:
            'startConsultation' => (new Appointment($var))->startConsultation(),
            'updateAppointmentStatus' => (new Appointment($var))->updateAppointmentStatus(),
            'getTodaysAppointments' => (new Appointment($var))->getTodaysAppointments(),
            'addAppointmentDoctor' => (new Appointment($var))->addAppointment(),
            'getAppointments' => (new Appointment($var))->getAppointments(),
            'modifyAppointmentDoctor'=> (new Appointment($var))->modifyAppointment(),
            //Consultations:
            'addConsultation' => (new Consultations($var))->addConsultation(),
            'getConsultationDetail' => (new Consultations($var))->getConsultationDetail(),
            'getConsultationHistory' => (new Consultations($var))->getConsultationHistory(),
            //Credit:
            'pushCredit' => (new Credit($var))->pushCredit(),
            //Patient GET:
            'getPatient' => (new PatientGet($var))->getPatient(),
            'searchPatient' => (new PatientGet($var))->searchPatient(),
            //Patient:
            'deletePatient' => (new PatientManager($var))->deletePatient(),
            'updatePatient' => (new PatientManager($var))->updatePatient(),
            'addPatientDoctor' => (new PatientManager($var))->addPatientDoctor(),
            //Doctor Statistics:
            'patientToday' => (new Statistics($var))->getPatientToday(),//
            'newPatientMonth' => (new Statistics($var))->getNewPatientMonth(),//
            'appointmentMonth' => (new Statistics($var))->getAppointmentMonth(),//
            'getMoneyMonth' => (new Statistics($var))->getMoneyMonth(),//
            'getAttendance' => (new Statistics($var))->getAttendance(),//
            'attendanceRate' => (new Statistics($var))->getAttendanceRate(),//
            'doctorCreditStat' => (new Statistics($var))->getDoctorCreditStat(),
            'officeCreditStat' => (new Statistics($var))->getOfficeCreditStat(),
            'AppointmentStat' => (new Statistics($var))->getAppointmentStat(),
            'doctorPatientStat' => (new Statistics($var))->getDoctorPatientStat(),

            //Csrf: 
            'getValidToken' => (new Csrf($var))->getValidToken(),
            default =>  $this->defaultNotif(),
        };
    }

    private function defaultNotif():array
    {
        Notifier::log('Erreur dans DoctorRouteur aucun match ne correspond', 'fatal');
        Notifier::console('Erreur RTE survenue');
        return [];
    }
}
