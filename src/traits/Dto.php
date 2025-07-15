<?php
// Ce trait Dto centralise et hydrate les données entrantes (formulaires, requêtes)
// Il prépare dynamiquement les tableaux pour alimenter les constructeurs des classes métier
namespace App\traits;

use App\core\Notifier;

trait Dto
{
    public function routerTell(string $action, array $data, string $loginId, string $userId): array
    {
        return match ($action) {
            
            //PatientManager:
            'addPatient' => [
                'doctorId' => $data['doctorId'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'loginId' => $loginId ?? '',
                'formName' => $data['formName'] ?? '',
                'lastName' => $data['lastName'] ?? '',
                'firstName' => $data['firstName'] ?? '',
                'birthDate' => $data['birthDate'] ?? '',
                'gender' => $data['gender'] ?? '',
                'address' => $data['address'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'socialSecurity' => $data['socialSecurity'] ?? '',
                'bloodGroup' => $data['bloodGroup'] ?? '',
                'allergy' => $data['allergy'] ?? '',
                'medicalHistory' => $data['medicalHistory'] ?? '',
            ],

            'addPatientDoctor' => [
                'doctorId' => $userId ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'loginId' => $loginId ?? '',
                'formName' => $data['formName'] ?? '',
                'lastName' => $data['lastName'] ?? '',
                'firstName' => $data['firstName'] ?? '',
                'birthDate' => $data['birthDate'] ?? '',
                'gender' => $data['gender'] ?? '',
                'address' => $data['address'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'socialSecurity' => $data['socialSecurity'] ?? '',
                'bloodGroup' => $data['bloodGroup'] ?? '',
                'allergy' => $data['allergy'] ?? '',
                'medicalHistory' => $data['medicalHistory'] ?? '',
            ],

            'updatePatientSecretary' => [
                'patientId' => $data['patientId'] ?? '',
                'loginId' => $loginId,
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'lastName' => $data['lastName'] ?? '',
                'firstName' => $data['firstName'] ?? '',
                'birthDate' => $data['birthDate'] ?? '',
                'address' => $data['address'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
            ],

            'updatePatient' => [
                'patientId' => $data['patientId'] ?? '',
                'loginId' => $loginId,
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'lastName' => $data['lastName'] ?? '',
                'firstName' => $data['firstName'] ?? '',
                'birthDate' => $data['birthDate'] ?? '',
                'address' => $data['address'] ?? '',
                'gender' => $data['gender'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'socialSecurity' => $data['socialSecurity'] ?? '',
                'bloodGroup' => $data['bloodGroup'] ?? '',
                'allergy' => $data['allergy'] ?? '',
                'medicalHistory' => $data['medicalHistory'] ?? '',
            ],

            'deletePatient' => [
                'doctorId' => $data['doctorId'] ?? '',
                'patientId' => $data['patientId'] ?? '',
                'csrf' => $data['csrf'] ?? '',
                'formName' => $data['formName'] ?? '',
                'loginId' => $loginId ?? '',
            ],

            //Patient GET:
            'searchPatient' => [
                'term' => $data['term'] ?? '',
            ],

            'searchPatientWithDoctor' => [
                'term' => $data['term'] ?? '',
            ],

            'getPatient' => [
                'doctorId' => $userId ?? ''
            ],

            //Credit
            'pushCredit' => [
                'appointmentId' => $data['appointmentId'] ?? '',
                'doctorId' => $userId ?? '',
                'consultationId' => $data['consultationId'] ?? '',
                'credit' => $data['price'] ?? '',
            ],
            //Consultation
            'addConsultation' => [
                'appointmentId' => $data['appointmentId'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'loginId' => $loginId ?? '',
                'doctorId' => $userId ?? '',
                'patientId' => $data['patientId'] ?? '',
                'diagnosis' => $data['diagnosis'] ?? '',
                'prescription' => $data['prescription'] ?? '',
                'title' => $data['title'] ?? '',
                'symptoms' => $data['symptoms'] ?? '',
            ],
            'getConsultationHistory' => [
                'appointmentId' => $data['appointmentId'] ?? '',
            ],
            'getConsultationDetail' => [
                'consultationId' => $data['consultationId'] ?? '',
            ],

            'getRetardCount' => [
                'doctorId' => $data['doctorId'] ?? '',
            ],

            //Appointment:
            'getAppointments' => [
                'doctorId' => $userId ?? '',
                'patientId' => $data['patientId'] ?? '',
            ],
            'getTodaysAppointments' => [
                'doctorId' => $userId ?? '',
            ],
            'startConsultation' => [
                'appointmentId' => $data['appointmentId'] ?? '',
            ],
            'endConsultation' => [
                'appointmentId' => $data['appointmentId'] ?? '',
            ],
            'markAbsent' => [
                'appointmentId' => $data['appointmentId'] ?? '',
            ],
            'getAppointmentDetail' => [
                'appointmentId' => $data['appointmentId'] ?? '',
            ],
            'updateAppointmentStatus' => [
                'appointmentId' => $data['appointmentId'] ?? '',
                'status' => $data['status'] ?? '',
            ],

            'addAppointment' => [
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'loginId' => $loginId ?? '',
                'doctorId' => $data['doctorId'] ?? '',
                'patientId' => $data['patientId'] ?? '',
                'secretaryId' => $userId ?? '',
                'appointmentDate' => $data['appointmentDate'] ?? '',
                'comment' => $data['comment'] ?? '',
                'reason' => $data['reason'] ?? '',
            ],

            'addAppointmentDoctor' => [
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'loginId' => $loginId ?? '',
                'doctorId' => $userId ?? '',
                'patientId' => $data['patientId'] ?? '',
                'appointmentdate' => $data['appointmentDate'] ?? '',
                'comment' => $data['comment'] ?? '',
                'reason' => $data['type'] ?? ''
            ],
            
            'changeAppointmentStatus' => [
                'appointmentId' => $data['appointmentId'] ?? '',
                'status' => $data['status'] ?? '',
            ],

            //secretary
            'editAppointment' => [
                'appointmentId' => $data['appointmentId'] ?? '',
                'csrf' => $data['csrf'] ?? '',
                'formName' => $data['formName'] ?? '',
                'loginId' => $loginId ?? '',
                'doctorId' => $data['doctorId'] ?? '',
                'comment' => $data['comment'] ?? '',
                'status' => $data['status'] ?? '',
                'appointmentdate' => $data['appointmentdate'] ?? '',
                'reason' => $data['reason'] ?? '',
            ],
            'modifyAppointmentDoctor' => [
                'appointmentId' => $data['appointmentId'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'appointmentdate' => $data['appointmentDate'] ?? '',
                'reason' => $data['type'] ?? '',
                'patientid' => $data['patientId'] ?? '',
                'status' => $data['status'] ?? '',
                'comment' => $data['comment'] ?? '',
                'loginId' => $loginId ?? '',
                'doctorId' => $userId ?? '',
            ],

            'deleteAppointment' => [
                'appointmentId' => $data['appointmentId'] ?? '',
                'doctorId' => $data['doctorId'] ?? '',
            ],

            'countAppointments' => [],

            //Message
            'getMessage' => [
                'userId' => $userId ?? '',
            ],

            'getSecretaryMessage' => [
                'userId' => '00000000-0000-0000-0000-000000000001',
            ],

            'getUnreadMessage' => [
                'secretaryId' => $userId ?? '',
            ],

            'sendMessage' => [
                'receiverId' => $data['doctorId'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'senderId' => $userId ?? '',
                'loginId' => $loginId ?? '',
                'object' => $data['object'] ?? '',
                'content' => $data['content'] ?? '',
            ],

            'sendMessageSecretary' => [
                'receiverId' => $data['doctorId'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'senderId' => $userId ?? '',
                'loginId' => $loginId ?? '',
                'object' => $data['object'] ?? '',
                'content' => $data['content'] ?? '',
            ],

            'sendMessageToSecretary' => [
                'receiverId' => '00000000-0000-0000-0000-000000000001',
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'senderId' => $userId ?? '',
                'loginId' => $loginId ?? '',
                'object' => $data['object'] ?? '',
                'content' => $data['content'] ?? '',
            ],

            'markIsRead' => [
                'messageId' => $data['messageId'] ?? '',
            ],

            //Pass
            'generateAccess' => [
                'doctorId' => $userId ?? '',
                'patientId' => $data['patientId'] ?? '',
            ],

            'updatePassword' => [
                'csrf' => $data['csrfToken'] ?? '',
                'formName' => $data['formName'] ?? '',
                'loginId' => $loginId ?? '',
                'newPassword' => $data['newPswd'] ?? '',
                'backPassword' => $data['currentPswd'] ?? '',
                'confirmPassword' => $data['confirmPswd'] ?? '',
            ],

            //DoctorManager
            'getDoctors' => [],

            'getDoctorsPatient' => [
                'patientId' => $userId ?? '',
            ],

            'getValidToken' => [
                'loginId' => $loginId ?? '',
                'formName' => $data['formName'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
            ],

            //OfficeHours
            'updateOpenHours' => [
                'loginId' => $loginId ?? '',
                'formName' => $data['formName'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'morningStart' => $data['morningStart'] ?? '',
                'morningEnd' => $data['morningEnd'] ?? '',
                'afternoonStart' => $data['afternoonStart'] ?? '',
                'afternoonEnd' => $data['afternoonEnd'] ?? '',
                'closedWeekdays' => $data['closedWeekdays'] ?? [],
                'closedDays' => $data['closedDays'] ?? '',
            ],

            'patientToday' => [
                'doctorId' => $userId ?? '',
            ],

            'newPatientMonth' => [
                'doctorId' => $userId ?? '',
            ],
            'getMoneyMonth' => [
                'doctorId' => $userId ?? '',
            ],
            'getAttendance' => [
                'doctorId' => $userId ?? '',
            ],

            'appointmentMonth' => [
                'doctorId' => $userId ?? '',
            ],

            'attendanceRate' => [
                'doctorId' => $userId ?? '',
            ],

            'doctorCreditStat' => [
                'doctorId' => $userId ?? '',
            ],

            'officeCreditStat' => [
                'doctorId' => $userId ?? '',
            ],

            'AppointmentStat' => [
                'doctorId' => $userId ?? '',
            ],

            'doctorPatientStat' => [
                'doctorId' => $userId ?? '',
            ],

            //Patient prise de rdv

            'getAvailable' => [
                'doctorId' => $data['doctorId'] ?? '',
            ],
            
            'takeAppointment' => [
                'loginId' => $loginId ?? '',
                'formName' => $data['formName'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'doctorId' => $data['doctorId'] ?? '',
                'patientId' => $userId ?? '',
                'appointmentDate' => $data['appointmentDate'] ?? '',
                'reason' => $data['reason'] ?? '',

            ],

            //Patient Info

            'getPatientInfo' => [
                'patientId' => $userId ?? '',
            ],
            
            'updatePatientInfo' => [
                'loginId' => $loginId ?? '',
                'formName' => $data['formName'] ?? '',
                'csrf' => $data['csrfToken'] ?? '',
                'patientId' => $userId ?? '',
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',

            ],

            default => $this->defaultNotif(),
        };
    }

    public function constructVar(array $entrance): void
    {
        foreach ($entrance as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    private function defaultNotif(): array
    {
        Notifier::log('Erreur dans Dto aucun match ne correspond', 'fatal');
        Notifier::console('Erreur Dto');
        return [];
    }
}
