<?php

declare(strict_types=1);

namespace App\controllers\patient;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use PDO;

final class Doctor {

    use Link;
    use Dto;

    private string $patientId;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }

    public function getDoctorsPatient(): array {
    $query = <<<SQL
                SELECT d.doctorid, CONCAT('Dr. ', d.lastName, ' ', d.firstName) AS fullName
                FROM doctors d
                JOIN associations a ON d.doctorid = a.iddoctor
                WHERE a.idpatient = :patientId AND a.archive = false
                ORDER BY d.lastName
                SQL;

    $statement = $this->connect->prepare($query);
    $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
    if (!$statement->execute()) {
        Notifier::console("Impossible de récupérer la liste des médecins");
        Notifier::log("Erreur getDoctorsPatient", 'critical');
        return [];
    }

    return ['doctors' => $statement->fetchAll(PDO::FETCH_ASSOC)];
}
}