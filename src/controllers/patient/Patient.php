<?php

declare(strict_types=1);

namespace App\controllers\patient;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use App\traits\SecureInfo;
use App\config\Config;
use PDO;

final class Patient
{

    use Link;
    use Dto;
    use SecureInfo;

    private string $loginId;
    private string $formName;
    private string $csrf;
    private string $patientId;
    private string $lastName;
    private string $firstName;
    private string $email;
    private string $phone;
    private string $address;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }

    public function getPatientInfo(): array
    {
        $key = Config::getCrypto();

        $query = <<<SQL
                SELECT 
                pgp_sym_decrypt(lastname::bytea, :key) AS lastName,
                pgp_sym_decrypt(firstname::bytea, :key) AS firstName, 
                email, 
                pgp_sym_decrypt(phone::bytea, :key) AS phone, 
                pgp_sym_decrypt(address::bytea, :key) AS address
                FROM patients
                WHERE patientid = :patientId
                SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les infos patients");
            Notifier::log("Erreur getPatientInfo", 'critical');
            return [];
        }

        $patient = ['patient' => $statement->fetch(PDO::FETCH_ASSOC)];

        return $patient;
    }


// NOTE CRUCIALE :  
// Pour garantir l’intégrité et la confidentialité des données médicales, la modification des champs nom, prénom et email est strictement interdite.  
// Permettre leur modification exposerait à des risques majeurs, notamment la compromission des historiques médicaux, diagnostics, et données pathologiques, 
// pouvant entraîner des erreurs cliniques graves.  
// Cette restriction témoigne de notre engagement à respecter la sécurité des patients et la fiabilité du système.
// Seuls les champs 'phone' et 'address' restent modifiables, toujours chiffrés pour protéger les données sensibles.

public function updatePatientInfo(): array 
{
    if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
        Notifier::log('Erreur dans Patient/updatePatientInfo: CSRF rejeté', 'critical');
        return [];
    }

    $key = Config::getCrypto();

    $query = <<<SQL
                UPDATE patients
                SET
                    phone = pgp_sym_encrypt(:phone, :key),
                    address = pgp_sym_encrypt(:address, :key)
                WHERE patientid = :patientId
                SQL;

    $statement = $this->connect->prepare($query);
    $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
    $statement->bindValue(':key', $key, PDO::PARAM_STR);
    $statement->bindValue(':phone', $this->phone, PDO::PARAM_STR);
    $statement->bindValue(':address', $this->address, PDO::PARAM_STR);

    if (!$statement->execute()) {
        Notifier::console("Impossible de mettre à jour les infos patients");
        Notifier::log("Erreur updatePatientInfo", 'critical');
        return [];
    }

    Notifier::popup('Vos données ont bien été mises à jour');
    return ['success' => true];
}

}
