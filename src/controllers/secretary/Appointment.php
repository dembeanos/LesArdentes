<?php

declare(strict_types=1);

namespace App\controllers\secretary;

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
    private string $appointmentDate;
    private string $comment;
    private string $reason;
    private string $status;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }


    //------------------------------------------------------------------GET Tout les rdv par date-----------------------------------------------------------
    
    public function getAllAppointment(): array
    {
        $update = <<<SQL
                      UPDATE appointments
                      SET status = 'passed'
                      WHERE appointmentdate < NOW()
                        AND status NOT IN ('ended', 'canceled', 'absent', 'passed');
                    SQL;

        $this->connect->exec($update);

        $key = Config::getCrypto();

        $query = <<<SQL
                SELECT 
                  a.appointmentid,
                  pgp_sym_decrypt(a.reason::bytea, :key) AS reason, 
                  pgp_sym_decrypt(a.comment::bytea, :key) AS comment,
                  CONCAT(
                    pgp_sym_decrypt(p.firstname::bytea, :key), ' ', 
                    pgp_sym_decrypt(p.lastname::bytea, :key)
                  ) AS patientName, 
                  a.appointmentdate, 
                  CONCAT('Dr.', ' ', d.lastName, ' ', d.firstName) AS doctorName,
                  a.status 
                FROM appointments a
                JOIN patients p ON p.patientid = a.idpatient
                JOIN doctors d ON a.iddoctor = d.doctorid
                WHERE a.status NOT IN ('ended', 'absent')
                ORDER BY a.creationdate DESC;
                SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Échec de la récupération des rdv');
            Notifier::log('Erreur dans Appointment Secretary méthode getAllAppointment, impossible de récupérer les infos', 'critical');
            return [];
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!$result) {
            Notifier::popup('Aucun rdv patient trouvée');
            Notifier::log('Erreur dans Appointment Secretary méthode getAllAppointment, résultat vide', 'critical');
            return [];
        }

        $this->varSanitizeDecode($result);

        return $result;
    }


    //------------------------------------------------------------------ADD Ajout RDV-----------------------------------------------------------

    public function addAppointment(): array
    {
        $queryRdvExist = <<<SQL
                            SELECT COUNT(*) FROM appointments 
                            WHERE iddoctor = :doctorId 
                            AND appointmentdate = :appointmentDate
                            SQL;

        $statementRdvExist = $this->connect->prepare($queryRdvExist);
        $statementRdvExist->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
        $statementRdvExist->bindValue(':appointmentDate', $this->appointmentDate, PDO::PARAM_STR);
        $statementRdvExist->execute();

        if ($statementRdvExist->fetchColumn() > 0) {
            Notifier::popup("Ce créneau est déjà réservé pour ce médecin.", 'error', 'appointmentDate');
            return [];
        }

        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Patient Manager/updatePatient: CSRF rejeté', 'critical');
            return [];
        }
        $key = Config::getCrypto();

        $query = <<<SQL
                    INSERT INTO appointments (iddoctor, idpatient, idsecretary, appointmentdate, status, reason, comment)
                    VALUES (:doctorId::uuid, :patientId::uuid, :secretaryId::uuid, :appointmentDate, 'reserved', pgp_sym_encrypt(:reason, :key), pgp_sym_encrypt(:comment, :key))
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
        $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
        $statement->bindValue(':secretaryId', $this->secretaryId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        $statement->bindValue(':appointmentDate', $this->appointmentDate, PDO::PARAM_STR);
        $statement->bindValue(':reason', $this->reason, PDO::PARAM_STR);
        $statement->bindValue(':comment', $this->comment ?? '', PDO::PARAM_STR);
        if (!$statement->execute()) {
            Notifier::popup("Échec de l'ajout d'un rdv");
            Notifier::log('Erreur dans Appointment Secretary méthode addAppointment, impossible d\'ajouter un rdv', 'critical');
            return [];
        }

        Notifier::popup('Rdv enregistré avec succès');
        return [];
    }

    //------------------------------------------------------------------UPDATE Change Status RDV-----------------------------------------------------------

    public function changeAppointmentStatus(): array
    {

        $query = <<<SQL
                    UPDATE appointments
                    SET
                        status = :status
                    WHERE appointmentid = :appointmentid::uuid
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':appointmentid', $this->appointmentId, PDO::PARAM_STR);
        $statement->bindValue(':status', $this->status, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Échec de la modification du status rdv');
            Notifier::log('Erreur dans Appointment Secretary méthode changeAppointmentStatus, impossible de modifier le status rdv', 'error');
            return [];
        }

        Notifier::popup('Status pris en compte');
        return [];
    }


    public function editAppointment(): array
    {

        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Appointment Secretary/modifyAppointment: CSRF rejeté', 'critical');
            return [];
        }
        $key = Config::getCrypto();

        $query = <<<SQL
                    UPDATE appointments
                    SET
                        reason = pgp_sym_encrypt(:reason, :key),
                        appointmentdate = :appointmentdate,
                        status = :status,
                        comment = pgp_sym_encrypt(:comment, :key)
                    WHERE appointmentid = :appointmentid::uuid
                      AND iddoctor = :doctorid::uuid
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':reason', $this->reason, PDO::PARAM_STR);
        $statement->bindValue(':appointmentdate', $this->appointmentDate, PDO::PARAM_STR);
        $statement->bindValue(':status', $this->status, PDO::PARAM_STR);
        $statement->bindValue(':comment', $this->comment ?? '', PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        $statement->bindValue(':appointmentid', $this->appointmentId, PDO::PARAM_STR);
        $statement->bindValue(':doctorid', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Échec de la modification d\'un rdv');
            Notifier::log('Erreur dans Appointment Secretary méthode modifyAppointment, impossible de modifier un rdv', 'critical');
            return [];
        }

        Notifier::popup('Rdv modifié avec succès');
        return [];
    }

    //------------------------------------------------------------------COUNT Compte les RDV du jour/Restant-----------------------------------------------------------

    public function countAppointments(): array|false
    {
        $query = <<<SQL
                SELECT 
                    COUNT(*) FILTER (WHERE appointmentdate::date = CURRENT_DATE) AS totalappointment
                    FROM appointments
                    WHERE status != 'ended';
                SQL;

        $statement = $this->connect->prepare($query);

        if (!$statement->execute()) {
            Notifier::console('Impossible de compter les rdv');
            Notifier::log('Erreur dans Appointment Secretary méthode countAppointments', 'error');
            return false;
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            Notifier::console('Aucun résultat de comptage trouvé');
            Notifier::log('Erreur dans Appointment Secretary méthode countAppointments Résultat vide', 'error');
            return false;
        }



        return ['totalappointment' => (int) $result['totalappointment']];
    }
}
