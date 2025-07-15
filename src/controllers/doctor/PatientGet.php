<?php
declare (strict_types = 1 );
namespace App\controllers\doctor;

use App\traits\Link;
use App\core\Notifier;
use App\config\Config;
use App\traits\Dto;
use App\traits\SecureInfo;
use PDO;
use App\traits\Sanitize;

final class PatientGet
{
    use Link;
    use Dto;
    use Sanitize;
    use SecureInfo;

    private string $doctorId;
    private string $term;
    private string $offset;
    private string $limit;

    public function __construct(array $var) {

        $this->constructVar($var);
    }


    public function getPatient(): array {
    $key = Config::getCrypto();
    $query = <<<SQL
        SELECT 
            p.patientid,
            pgp_sym_decrypt(p.lastname::bytea, :key) AS lastname, 
            pgp_sym_decrypt(p.firstname::bytea, :key) AS firstname, 
            pgp_sym_decrypt(p.birthdate::bytea, :key) AS birthdate, 
            pgp_sym_decrypt(p.gender::bytea, :key) AS gender, 
            pgp_sym_decrypt(p.address::bytea, :key) AS address, 
            email, 
            pgp_sym_decrypt(p.phone::bytea, :key) AS phone, 
            pgp_sym_decrypt(p.social_security::bytea, :key) AS social_security, 
            pgp_sym_decrypt(p.blood_group::bytea, :key) AS blood_group, 
            pgp_sym_decrypt(p.allergy::bytea, :key) AS allergy, 
            pgp_sym_decrypt(p.medical_history::bytea, :key) AS medical_history
        FROM patients p
        JOIN associations a ON a.idpatient = p.patientid
        WHERE a.iddoctor = :doctorId::uuid
          AND a.archive = false
    SQL;

    $statement = $this->connect->prepare($query);
    $statement->bindValue(':doctorId', (string)$this->doctorId, PDO::PARAM_STR);
    $statement->bindValue(':key', (string)$key, PDO::PARAM_STR);

    if (!$statement->execute()) {
        Notifier::console('Échec de la récupération des données patient');
        Notifier::log('Erreur dans PatientManager méthode getPatient, impossible de récupérer les infos', 'critical');
        return [];
    }

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!$results) {
        Notifier::console('Aucune donnée patient trouvée');
        Notifier::log('Erreur dans PatientManager méthode getPatient, résultat vide', 'critical');
        return [];
    }

    $this->varSanitizeDecode($results);

    return $results;
}

 public function searchPatient(): array
{
    $key = Config::getCrypto();

    $query = <<<SQL
    SELECT
      p.patientid,
      pgp_sym_decrypt(p.lastname::bytea, :key) || ' ' || pgp_sym_decrypt(p.firstname::bytea, :key) AS patient_fullname
    FROM patients p
    JOIN associations a ON a.idpatient = p.patientid AND a.archive = false
    JOIN doctors d ON d.doctorid = a.iddoctor
    WHERE a.archive = false
      AND (
        pgp_sym_decrypt(p.lastname::bytea, :key) ILIKE :term OR
        pgp_sym_decrypt(p.firstname::bytea, :key) ILIKE :term OR
        p.email ILIKE :term OR
        pgp_sym_decrypt(p.phone::bytea, :key) ILIKE :term
      )
    GROUP BY p.patientid, patient_fullname
    LIMIT 10;
SQL;

    $statement = $this->connect->prepare($query);
    $likeTerm = '%' . $this->term . '%';

    $statement->bindValue(':key', $key, PDO::PARAM_STR);
    $statement->bindValue(':term', $likeTerm, PDO::PARAM_STR);

    if (!$statement->execute()) {
        Notifier::console('Échec de la recherche patient');
        Notifier::log('Erreur dans PatientManager méthode searchPatient, Impossible de récupérer les résultats', 'error');
        return [];
    }

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!$results) {
        Notifier::input('Aucun résultat', 'error', 'searchPatient');
        Notifier::console('Aucune donnée ne correspond');
        return [];
    }

    $this->varSanitizeDecode($results);

    return $results;
}


 
}