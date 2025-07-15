<?php

declare(strict_types=1);

namespace App\controllers\secretary;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use PDO;

final class DoctorManager {

    use Link;
    use Dto;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }

    public function getDoctors(): array {
    $query = <<<SQL
                SELECT doctorid, CONCAT('Dr. ', lastName, ' ', firstName) 
                AS fullName FROM doctors ORDER BY lastName
                SQL;

    $statement = $this->connect->prepare($query);
    if (!$statement->execute()) {
        Notifier::console("Impossible de récupérer la liste des médecins");
        Notifier::log("Erreur getDoctors", 'error');
        return [];
    }

    return ['doctors' => $statement->fetchAll(PDO::FETCH_ASSOC)];
}
}