<?php

declare (strict_types = 1 );

namespace App\auth;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use App\traits\SecureInfo;
use PDO;


final class Pass {
    use Link;
    use Dto;
    use SecureInfo;

    private string $doctorId;
    private string $patientId;
    private string $newPassword;
    private string $backPassword;
    private string $confirmPassword;
    private string $loginId;
    private string $csrf;
    private string $formName;

    public function __construct($var)
    {
        $this->constructVar($var);
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

public function generateAccess(): array {
    // Génère login et password
    $login = 'pat_' . bin2hex(random_bytes(5));
    $password = $this->generateSecurePassword(8);
    $passHash = password_hash($password, PASSWORD_DEFAULT);

    // Vérifier que le doctor existe
    $queryCheck = "SELECT COUNT(*) FROM doctors WHERE doctorid = :doctorId";
    $statementCheckDoctor = $this->connect->prepare($queryCheck);
    $statementCheckDoctor->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
    $statementCheckDoctor->execute();
    $count = $statementCheckDoctor->fetchColumn();

    if ($count > 0) {
        $query = <<<SQL
            UPDATE logins l
            SET password = :password,
                username = :login
            FROM patients p
            WHERE p.patientid = :patientid
              AND p.idlogin = l.loginid
        SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':password', $passHash, PDO::PARAM_STR);
        $statement->bindValue(':login', $login, PDO::PARAM_STR);
        $statement->bindValue(':patientid', $this->patientId, PDO::PARAM_STR);
        
        if (!$statement->execute()) {
            Notifier::popup("Erreur lors de la mise à jour des accès");
            return [];
        }
    } else {
        // DoctorId invalide
        Notifier::popup("Autorisation rejetée");
        return [];
    }

    // Retourner login et mot de passe non hashé pour affichage au docteur et communication de son accès au patient
    return [
        'login' => $login,
        'password' => $password,
    ];
}

public function updatePassword(): array
{
    if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
        Notifier::log('Erreur dans Patient/updatePatientInfo: CSRF rejeté', 'critical');
        return [];
    }

    if ($this->newPassword !== $this->confirmPassword) {
        Notifier::popup("Le nouveau mot de passe et la confirmation ne correspondent pas");
        return [];
    }

    $hash = password_hash($this->newPassword, PASSWORD_DEFAULT);
    

    $query = "UPDATE logins SET password = :password WHERE loginid = :loginId";
    $stmt = $this->connect->prepare($query);
    $stmt->bindValue(':password', $hash, PDO::PARAM_STR);
    $stmt->bindValue('loginId', $this->loginId, PDO::PARAM_STR);

    if (!$stmt->execute()) {
        Notifier::popup("Erreur lors de la mise à jour du mot de passe");
        return [];
    }

    Notifier::log("Mot de passe mis à jour pour le login ID : {$this->loginId}", 'info');
    Notifier::popup("Mot de passe mis à jour avec succès");
    return [];
}


}

?>