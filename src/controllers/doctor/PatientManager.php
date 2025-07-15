<?php
declare (strict_types = 1 );
namespace App\controllers\doctor;

use App\traits\Link;
use App\core\Notifier;
use App\config\Config;
use App\traits\Dto;
use App\traits\SecureInfo;
use Exception;
use PDO;
use App\traits\Sanitize;


final class PatientManager {

    use Link;
    use Dto;
    use Sanitize;
    use SecureInfo;

    private string $doctorId;
    private string $patientId;
    private string $loginId;
    private string $csrf;
    private string $lastName;
    private string $firstName;
    private string $birthDate;
    private string $gender;
    private string $address;
    private string $email;
    private string $phone;
    private string $socialSecurity;
    private string $bloodGroup;
    private string $allergy;
    private string $medicalHistory;
    private string $formName;

    public function __construct(array $var)
    {

        $this->constructVar($var);
    }

    public function deletePatient(): array {

        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Patient Manager/deletePatient: csrf rejeté', 'critical');

            return [];
        }
        
        $query = <<<SQL
                    UPDATE associations
                    SET archive = true,
                        archiveddate = NOW()
                    WHERE patientid = :patientId::uuid
                    AND iddoctor = :doctorId::uuid;
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
        if (!$statement->execute()) {
            Notifier::console('Echec de la suppression du patient');
            Notifier::log('Erreur dans PatientManager méthode deletePatient, Impossible de suppression du patient:' . $this->patientId . 'docteur:' . $this->doctorId, 'Error');
            return [];
        }

        Notifier::popup('Patient supprimmé avec succès');
        return [];
    }

    public function updatePatient(): array {

        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Patient Manager/updatePatient: CSRF rejeté', 'critical');
            return [];
        }

        $key = Config::getCrypto();


        $query = <<<SQL
                    UPDATE patients SET
                        lastname = pgp_sym_encrypt(:lastname, :key),
                        firstname = pgp_sym_encrypt(:firstname, :key),
                        birthdate = pgp_sym_encrypt(:birthdate, :key),
                        gender = pgp_sym_encrypt(:gender, :key),
                        address = pgp_sym_encrypt(:address, :key),
                        email = :email,
                        phone = pgp_sym_encrypt(:phone, :key),
                        social_security = pgp_sym_encrypt(:socialSecurity, :key),
                        blood_group = pgp_sym_encrypt(:bloodGroup, :key),
                        allergy = pgp_sym_encrypt(:allergy, :key),
                        medical_history = pgp_sym_encrypt(:medicalHistory, :key)
                    WHERE patientid = :patientId::uuid
                    SQL;


        $statement = $this->connect->prepare($query);
        $statement->bindValue(':lastname', $this->lastName, PDO::PARAM_STR);
        $statement->bindValue(':firstname', $this->firstName, PDO::PARAM_STR);
        $statement->bindValue(':birthdate', $this->birthDate, PDO::PARAM_STR);
        $statement->bindValue(':gender', $this->gender, PDO::PARAM_STR);
        $statement->bindValue(':address', $this->address, PDO::PARAM_STR);
        $statement->bindValue(':email', $this->email, PDO::PARAM_STR);
        $statement->bindValue(':phone', $this->phone, PDO::PARAM_STR);
        $statement->bindValue(':socialSecurity', $this->socialSecurity, PDO::PARAM_STR);
        $statement->bindValue(':bloodGroup', $this->bloodGroup, PDO::PARAM_STR);
        $statement->bindValue(':allergy', $this->allergy, PDO::PARAM_STR);
        $statement->bindValue(':medicalHistory', $this->medicalHistory, PDO::PARAM_STR);
        $statement->bindValue(':patientId', $this->patientId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        if (!$statement->execute()) {
            Notifier::console('Echec de la mise a jour du patient');
            Notifier::log('Erreur dans PatientManager méthode updatePatient, Impossible d\'éffectuer la mise a jour du patient:'
                . $this->patientId . 'docteur:' . $this->doctorId, 'Error');

            return [];
        }
        Notifier::popup('Fiche Patient mise a jour avec succès');
        return [];
    }




   public function addPatientDoctor(): array
{
    if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
        Notifier::log('CSRF rejeté dans addPatient', 'critical');
        return ['error' => 'csrf_invalid'];
    }

    $key = Config::getCrypto();
    $this->connect->beginTransaction();

    try {
        $existingPatientId = $this->getExistingPatientId();

        // Cas : le patient existe déjà
        if ($existingPatientId) {
            if (!$this->linkPatientToDoctor($existingPatientId)) {
                $this->connect->rollBack();
                return ['error' => 'link_failed'];
            }

            $this->connect->commit();
            Notifier::popup('Patient déjà existant associé à ce médecin.');

            return [
                'login' => null,
                'password' => null,
                'patientId' => $existingPatientId
            ];
        }

        $loginData = $this->createPatientLogin();
        if (!$loginData) {
            $this->connect->rollBack();
            return [];
        }

        $patientId = $this->insertPatient($loginData['loginId'], $key);
        if (!$patientId) {
            $this->connect->rollBack();
            return [];
        }

        if (!$this->linkPatientToDoctor($patientId)) {
            $this->connect->rollBack();
            return [];
        }

        $this->connect->commit();

        return [
            'login' => $loginData['login'],
            'password' => $loginData['password'],
            'patientId' => $patientId
        ];
    } catch (Exception $e) {
        $this->connect->rollBack();
        Notifier::log("Erreur globale addPatient : " . $e->getMessage(), 'critical');
        return [];
    }
}


    public function getExistingPatientId(): ?string
    {
        $query = "SELECT patientid FROM patients WHERE email = :email LIMIT 1";
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':email', $this->email, PDO::PARAM_STR);

        if ($statement->execute()) {
            $patientId = $statement->fetchColumn();
            return $patientId ?: null;
        }

        return null;
    }

    private function generateSecurePassword(int $length = 8): string
    {
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $specials = '!@#$%^&*';
        $all = $lower . $upper . $digits . $specials;

        //On prend un caractère de chaque catégorie pour assurer la diversité
        $password = '';
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $specials[random_int(0, strlen($specials) - 1)];

        // Complète avec des caractères aléatoires
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Mélange les caractères pour casser l'ordre fixe
        return str_shuffle($password);
    }


    public function createPatientLogin(): array|false
    {
        $login = 'pat_' . bin2hex(random_bytes(5));
        $password = $this->generateSecurePassword(8);
        $passHash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO logins (username, password, role) VALUES (:login, :password, 'patient')";
        $insert = $this->connect->prepare($query);
        $insert->bindValue(':login', $login);
        $insert->bindValue(':password', $passHash);

        if (!$insert->execute()) {
            Notifier::log('Erreur lors de la création du login patient', 'critical');
            return false;
        }

        $stmt = $this->connect->prepare("SELECT loginid FROM logins WHERE username = :login");
        $stmt->bindValue(':login', $login);
        if (!$stmt->execute()) {
            Notifier::log('Erreur lors de la récupération du loginId patient', 'critical');
            return false;
        }

        $loginId = $stmt->fetchColumn();
        if (!$loginId) {
            Notifier::log('LoginId introuvable après création', 'critical');
            return false;
        }

        return ['loginId' => $loginId, 'login' => $login, 'password' => $password];
    }

    public function insertPatient(string $loginId, string $key): string|false
    {
        $query = <<<SQL
        INSERT INTO patients (
            idlogin, lastname, firstname, birthdate, gender, address, email,
            phone, social_security, blood_group, allergy, medical_history
        ) VALUES (
            :loginId, 
            pgp_sym_encrypt(:lastname, :key), pgp_sym_encrypt(:firstname, :key),
            pgp_sym_encrypt(:birthdate, :key), pgp_sym_encrypt(:gender, :key),
            pgp_sym_encrypt(:address, :key), :email, pgp_sym_encrypt(:phone, :key),
            pgp_sym_encrypt(:socialSecurity, :key), pgp_sym_encrypt(:bloodGroup, :key),
            pgp_sym_encrypt(:allergy, :key), pgp_sym_encrypt(:medicalHistory, :key)
        )
        RETURNING patientid
    SQL;

        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':loginId', $loginId);
        $stmt->bindValue(':key', $key);
        $stmt->bindValue(':lastname', $this->lastName);
        $stmt->bindValue(':firstname', $this->firstName);
        $stmt->bindValue(':birthdate', $this->birthDate);
        $stmt->bindValue(':gender', $this->gender);
        $stmt->bindValue(':address', $this->address);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':phone', $this->phone);
        $stmt->bindValue(':socialSecurity', $this->socialSecurity);
        $stmt->bindValue(':bloodGroup', $this->bloodGroup);
        $stmt->bindValue(':allergy', $this->allergy);
        $stmt->bindValue(':medicalHistory', $this->medicalHistory);

        if (!$stmt->execute()) {
            Notifier::log('Erreur lors de l\'insertion du patient', 'critical');
            return false;
        }

        return $stmt->fetchColumn() ?: false;
    }

    public function linkPatientToDoctor(string $patientId): bool
    {
        $query = "INSERT INTO associations (iddoctor, idpatient, linkdate, archive) VALUES (:doctorId::uuid, :patientId::uuid, NOW(), false)";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':doctorId', $this->doctorId);
        $stmt->bindValue(':patientId', $patientId);

        if (!$stmt->execute()) {
            Notifier::log('Erreur lors de l\'association patient-docteur', 'critical');
            return false;
        }

        return true;
    }
}
?>