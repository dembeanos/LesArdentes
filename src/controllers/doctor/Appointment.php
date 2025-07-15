<?php

declare(strict_types=1);

namespace App\controllers\doctor;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use App\traits\SecureInfo;
use App\config\Config;
use PDO;
use App\traits\Sanitize;

final class Appointment
{

    use Link;
    use Dto;
    use SecureInfo;
    use Sanitize;


    private string $doctorId;
    private string $patientId;
    private string $appointmentId;
    private string $loginId;
    private string $from;
    private string $to;
    private string $csrf;
    private string $formName;
    private string $secretaryId;
    private string $appointmentdate;
    private string $comment;
    private string $reason;
    private string $status;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }


    public function getAppointments(): array {

        $key = Config::getCrypto();

        $query = <<<SQL
                    SELECT
                        a.appointmentid,
                        p.patientid,
                        to_char(a.appointmentdate AT TIME ZONE 'Europe/Paris', 'DD/MM/YYYY') AS date_fr,
                        to_char(a.appointmentdate AT TIME ZONE 'Europe/Paris', 'HH24:MI')  AS time_fr,
                        CONCAT(
                          pgp_sym_decrypt(p.firstname::bytea, :key),
                          ' ',
                          pgp_sym_decrypt(p.lastname::bytea, :key)
                        ) AS patientname,
                        pgp_sym_decrypt(a.reason::bytea, :key) AS type,
                        pgp_sym_decrypt(a.comment::bytea, :key) AS comment,
                        a.status
                      FROM appointments a
                      JOIN patients p ON p.patientid = a.idpatient
                      WHERE a.iddoctor = :doctorid::uuid
                        AND a.status != 'ended'
                      ORDER BY a.creationdate DESC
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorid', $this->doctorId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console('Échec de la récupération des rdv');
            Notifier::log('Erreur dans Appointment méthode getAppointment, impossible de récupérer les infos', 'critical');
            return [];
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->varSanitizeDecode($result);

        return $result;
    }


    public function getTodaysAppointments(): array
    {

        $key = Config::getCrypto();

        $query = <<<SQL
                SELECT 
                    a.appointmentid,
                    p.patientid,
                    a.appointmentdate, 
                    CONCAT(pgp_sym_decrypt(p.firstname::bytea, :key), ' ', pgp_sym_decrypt(p.lastname::bytea, :key)) AS patientName, 
                    pgp_sym_decrypt(a.reason::bytea, :key) AS reason, 
                    a.status 
                FROM appointments a
                JOIN patients p ON p.patientid = a.idpatient
                WHERE a.iddoctor = :doctorid::uuid
                AND a.status != 'ended'
                AND a.appointmentdate::date = CURRENT_DATE
                ORDER BY a.appointmentdate DESC
                SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorid', $this->doctorId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console('Échec de la récupération des rdv');
            Notifier::log('Erreur dans Appointment méthode getAppointment, impossible de récupérer les infos', 'critical');
            return [];
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!$result) {
            Notifier::console('Aucun rdv patient trouvée');
            Notifier::log('Erreur dans Appointment méthode getAppointment, résultat vide', 'critical');
            return [];
        }

        $this->varSanitizeDecode($result);

        return $result;
    }

    public function addAppointment(): array
    {

        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Patient Manager/updatePatient: CSRF rejeté', 'critical');
            return [];
        }
        $key = Config::getCrypto();

        $query = <<<SQL
        INSERT INTO appointments (iddoctor, idpatient, idsecretary, appointmentdate, status, reason, comment)
        VALUES (:doctorId::uuid, :patientId::uuid, :secretaryId::uuid, :appointmentdate, 'reserved', pgp_sym_encrypt(:reason, :key), pgp_sym_encrypt(:comment, :key))
    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
        $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
        $statement->bindValue(':secretaryId', $this->secretaryId ?? null, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        $statement->bindValue(':appointmentdate', $this->appointmentdate, PDO::PARAM_STR); // format datetime
        $statement->bindValue(':reason', $this->reason, PDO::PARAM_STR);
        $statement->bindValue(':comment', $this->comment ?? '', PDO::PARAM_STR);
        
        if (!$statement->execute()) {
            Notifier::popup('Échec de l\'ajout d\'un rdv');
            Notifier::log('Erreur dans Appointment méthode addAppointment, impossible d\'ajouter un rdv', 'critical');
            return [];
        }

        Notifier::popup('Rdv enregistré avec succès');
        return [];
    }

    public function modifyAppointment(): array
    {

        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Patient Manager/updatePatient: CSRF rejeté', 'critical');
            return[];
        }
        $key = Config::getCrypto();

        $query = <<<SQL
        UPDATE appointments
        SET
            reason = pgp_sym_encrypt(:reason, :key),
            appointmentdate = :appointmentdate,
            comment = pgp_sym_encrypt(:comment, :key)
        WHERE appointmentid = :appointmentid::uuid
          AND iddoctor = :doctorid::uuid
    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':reason', $this->reason, PDO::PARAM_STR);
        $statement->bindValue(':appointmentdate', $this->appointmentdate, PDO::PARAM_STR); // date/heure du rdv
        $statement->bindValue(':comment', $this->comment ?? '', PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        $statement->bindValue(':appointmentid', $this->appointmentId, PDO::PARAM_STR);
        $statement->bindValue(':doctorid', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Échec de la modification d\'un rdv');
            Notifier::log('Erreur dans Appointment méthode modifyAppointment, impossible de modifier un rdv', 'critical');
            return [];
        }

        Notifier::popup('Rdv modifié avec succès');
        return [];
    }

    public function startConsultation(): array
    {

        $query = <<<SQL
        UPDATE appointments
        SET status = 'progress'
        WHERE appointmentid = :appointmentid::uuid
    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':appointmentid', $this->appointmentId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Échec dud démmarrage du rdv');
            Notifier::log('Erreur dans Appointment méthode startConsultation, impossible de demarrer un rdv', 'critical');
            return [];
        }

        Notifier::popup('Rdv démarré');
        return [];
    }

    public function updateAppointmentStatus(): array
    {

        $query = <<<SQL
        UPDATE appointments
        SET status = :status
        WHERE appointmentid = :appointmentid::uuid
    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':appointmentid', $this->appointmentId, PDO::PARAM_STR);
        $statement->bindValue(':status', $this->status, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Échec de la cloture du rdv');
            Notifier::log('Erreur dans Appointment méthode endConsultation, impossible de terminer un rdv', 'critical');
            return [];
        }

        if($this->status === 'ended'){
        Notifier::popup('Rendez-vous terminé');
        return [];
        } else {
            Notifier::popup('Rendez-vous annulé');
        return [];
        }
    }

}