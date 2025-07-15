<?php

declare(strict_types=1);

namespace App\controllers\patient;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use App\traits\SecureInfo;
use App\config\Config;
use PDO;

final class Calendar {

    use Link;
    use Dto;
    use SecureInfo;

    private string $doctorId;
    private string $patientId;
    private string $loginId;
    private string $reason;
    private string $appointmentDate;
    private string $csrf;
    private string $formName;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }

public function getAvailable(): array
{
    // 1. Récupérer les infos horaires
    $queryOffice = <<<SQL
        SELECT 
            closed_weekdays,
            closed_days,
            to_char(morning_start, 'HH24:MI:SS') AS morning_start,
            to_char(morning_end, 'HH24:MI:SS') AS morning_end,
            to_char(afternoon_start, 'HH24:MI:SS') AS afternoon_start,
            to_char(afternoon_end, 'HH24:MI:SS') AS afternoon_end
        FROM officehours
        WHERE id = 'default'
    SQL;

    $stmtOffice = $this->connect->query($queryOffice);
    $office = $stmtOffice->fetch(PDO::FETCH_ASSOC);

    // 2. Récupérer les rendez-vous pris
    $queryAppts = <<<SQL
        SELECT 
            to_char(appointmentdate, 'YYYY-MM-DD"T"HH24:MI:SS') AS start,
            to_char(appointmentdate + INTERVAL '30 minutes', 'YYYY-MM-DD"T"HH24:MI:SS') AS end,
            'Déjà pris' AS title
        FROM appointments
        WHERE iddoctor = :doctorId
    SQL;

    $stmtAppts = $this->connect->prepare($queryAppts);
    $stmtAppts->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
    $stmtAppts->execute();
    $notAvailable = $stmtAppts->fetchAll(PDO::FETCH_ASSOC);

    // 3. On retourne brut, sans traitement
    return [
        'closedOffice' => [
            'closed_weekdays' => $office['closed_weekdays'],
            'closed_days' => $office['closed_days'],
            'opening_hours' => [
                [
                    'startTime' => $office['morning_start'],
                    'endTime' => $office['morning_end'],
                    'daysOfWeek' => null
                ],
                [
                    'startTime' => $office['afternoon_start'],
                    'endTime' => $office['afternoon_end'],
                    'daysOfWeek' => null
                ]
            ]
        ],
        'notAvailable' => $notAvailable
    ];
}

public function takeAppointment():array {

    if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Appointment Secretary/modifyAppointment: CSRF rejeté', 'critical');
            return [];
        }

        $key = Config::getCrypto();

    $sql = <<<SQL
    INSERT INTO appointments (
        iddoctor, idpatient, idsecretary, appointmentdate,
        status, reason, comment
    ) VALUES (
        :iddoctor, :idpatient, null, :appointmentdate,
        :status, pgp_sym_encrypt(:reason, :key), pgp_sym_encrypt(:comment, :key)
    )
    SQL;

    $stmt = $this->connect->prepare($sql);

    $stmt->bindValue(':iddoctor', $this->doctorId, PDO::PARAM_STR );
    $stmt->bindValue(':idpatient', $this->patientId, PDO::PARAM_STR);
    $stmt->bindValue(':appointmentdate', $this->appointmentDate, PDO::PARAM_STR);
    $stmt->bindValue(':status', 'reserved', PDO::PARAM_STR);
    $stmt->bindValue(':key', $key, PDO::PARAM_STR);
    $stmt->bindValue(':reason', $this->reason, PDO::PARAM_STR);
    $stmt->bindValue(':comment', 'Néant', PDO::PARAM_STR);

    if(!$stmt->execute()){
        Notifier::popup("Echec de l'enregistrement de votre Rdv veuillez contacter le secretariat");
        Notifier::log("Erreur lors de la prise de rdv dans takeAppointment /Patient ", 'fatal');
    }
    Notifier::popup("Votre rendez-vous a bien été réservé");
    return [];
}




}