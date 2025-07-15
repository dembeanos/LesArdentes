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

final class Consultations
{

    use SecureInfo;
    use Sanitize;
    use Link;
    use Dto;

    private string $doctorId;
    private string $patientId;
    private string $loginId;
    private string $appointmentId;
    private string $consultationId;
    private string $diagnosis;
    private string $prescription;
    private string $title;
    private string $csrf;
    private string $formName;
    private string $symptoms;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }


    public function addConsultation(): array
    {
        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Appointment Secretary/modifyAppointment: CSRF rejeté', 'critical');
            return [];
        }

        $now = date('Y-m-d H:i:s');
        $key = Config::getCrypto();
        $query = <<<SQL
                    INSERT INTO consultations (
                        idappointment, 
                        iddoctor, 
                        idpatient, 
                        consultation_date,
                        symptoms, 
                        diagnosis, 
                        prescription,
                        title
                        )
                    VALUES (
                        :idappointment, 
                        :doctorId, 
                        :patientId, 
                        :consultationDate,
                        pgp_sym_encrypt(:symptoms, :key),
                        pgp_sym_encrypt(:diagnosis, :key), 
                        pgp_sym_encrypt(:prescription, :key),
                        pgp_sym_encrypt(:title, :key)
                        );
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        $statement->bindValue(':idappointment', $this->appointmentId, PDO::PARAM_STR);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
        $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
        $statement->bindValue(':consultationDate', $now, PDO::PARAM_STR);
        $statement->bindValue(':symptoms', $this->symptoms, PDO::PARAM_STR);
        $statement->bindValue(':diagnosis', $this->diagnosis, PDO::PARAM_STR);
        $statement->bindValue(':prescription', $this->prescription, PDO::PARAM_STR);
        $statement->bindValue(':title', $this->title, PDO::PARAM_STR);
        if ($statement->execute()) {

            Notifier::popup('Consultation ajoutée avec succès');
            return [];
        } else {
            Notifier::popup('Erreur lors de l\'ajout de la consultation');
            Notifier::log('Erreur dans Consultations methode addConsultation', 'error');
        }
        return [];
    }


    public function getConsultationHistory(): array
    {

        $key = Config::getCrypto();

        $query = <<<SQL
                SELECT c.consultationid, c.consultation_date, pgp_sym_decrypt(title::bytea, :key) AS title
                FROM consultations c
                WHERE idappointment = :appointmentid::uuid
                ORDER BY consultation_date DESC
                SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':appointmentid', $this->appointmentId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup("Impossible de récupérer l'historique");
            Notifier::log("Erreur dans getConsultationHistory", 'critical');
            return [];
        }

        return ['data' => $statement->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function getConsultationDetail(): array
    {

        $key = Config::getCrypto();

        $query = <<<SQL
        SELECT 
            pgp_sym_decrypt(c.title::bytea, :key) AS title,
            pgp_sym_decrypt(c.diagnosis::bytea, :key) AS diagnosis,
            pgp_sym_decrypt(c.symptoms::bytea, :key) AS symptoms,
            pgp_sym_decrypt(c.prescription::bytea, :key) AS prescription,
            c.consultation_date
        FROM consultations c
        WHERE c.consultationid = :consultationid::uuid
    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':consultationid', $this->consultationId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup("Consultation introuvable");
            Notifier::log("Erreur dans getConsultation", 'critical');
            return [];
        }

        return ['data' => $statement->fetch(PDO::FETCH_ASSOC)];
    }
}
