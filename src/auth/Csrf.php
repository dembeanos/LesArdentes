<?php

declare(strict_types=1);

namespace App\auth;

use App\traits\Clean;
use App\traits\Link;
use App\traits\SecureInfo;
use App\secure\Secure;
use App\traits\Dto;
use App\core\Notifier;
use PDO;
use DateTime;
use DateInterval;

class Csrf
{
    use Link;
    use Clean;
    use SecureInfo;
    use Dto;

    private string $loginId;
    private string $formName;
    private string $csrf;

    public function __construct(array $var)
    {
        $this->constructVar($var);
    }


    private function secureAuthorisation(): string|false
    {

        $ip        = $this->getIp();
        $userAgent = $this->getUserAgent();
        $secure    = new Secure($this->loginId, $ip, $userAgent);
        $secureId  = $secure->checkMasterToken();

        if ($secureId !== false) {
            return (string)$secureId;
        }

        return false;
    }


    private function generateToken(): string|false
    {
        $secureId = $this->secureAuthorisation();
        if ($secureId === false) {
            Notifier::log('Erreur Csrf Methode generateToken Vérification CSRF échouée : session non sécurisée MasterToken rejeté', 'fatal');
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiration = (new DateTime())
            ->add(new DateInterval('PT15M'))
            ->format('Y-m-d H:i:s');

        $query = <<<SQL
                INSERT INTO csrf (idsecure, token, formname, expiration) 
                VALUES (:secureid::uuid, :token, :formname, :expiration)
                SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':secureid',  $secureId,    PDO::PARAM_STR);
        $statement->bindValue(':token',     $token,       PDO::PARAM_STR);
        $statement->bindValue(':formname',  $this->formName, PDO::PARAM_STR);
        $statement->bindValue(':expiration', $expiration,  PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::log("Erreur Csrf: methode: generateToken, lors de l\'enregistrement du token CSRF", 'error');
            return false;
        }

        return $token;
    }

 
    public function checkCsrf(): bool
    {
        $secureId = $this->secureAuthorisation();
        if ($secureId === false) {
            Notifier::log('Erreur Csrf Methode checkCsrf Vérification CSRF échouée : session non sécurisée MasterToken rejeté', 'fatal');
            return false;
        }

        $query = <<<SQL
                SELECT token, expiration 
                FROM csrf 
                WHERE idsecure = :secureid
                AND token = :token 
                AND formname = :formname
                SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':secureid', $secureId,       PDO::PARAM_STR);
        $statement->bindValue(':token',     $this->csrf,  PDO::PARAM_STR);
        $statement->bindValue(':formname',  $this->formName, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            Notifier::log("Erreur Csrf méthode: checkCsrf lors du resultat secureId={$secureId} | token={$this->csrf} | formName={$this->formName}", 'fatal');

            return false;
        }

        $now = new DateTime();
        $expiration = DateTime::createFromFormat('Y-m-d H:i:s', $row['expiration']);

        if ($expiration < $now) {
            Notifier::log('Token CSRF expiré', 'error');
            return false;
        }

        return true;
    }


    private function refreshCsrf(): string|false
    {
        $secureId = $this->secureAuthorisation();
        if ($secureId === false) {
            Notifier::log('Erreur Csrf Methode refreshToken Vérification CSRF échouée : session non sécurisée MasterToken rejeté', 'fatal');
            return false;
        }

        // Vérifier s’il existe un token valide pour ce form
        $selectQuery = <<<SQL
        SELECT token, expiration 
        FROM csrf 
        WHERE idsecure = :secureid::uuid 
        AND formname = :formname
        SQL;
        $selectStmt = $this->connect->prepare($selectQuery);
        $selectStmt->bindValue(':secureid',  $secureId, PDO::PARAM_STR);
        $selectStmt->bindValue(':formname',  $this->formName, PDO::PARAM_STR);
        $selectStmt->execute();
        $existing = $selectStmt->fetch(PDO::FETCH_ASSOC);

        // Si un token existe
        if ($existing) {
            $now        = new \DateTime();
            $expiration = \DateTime::createFromFormat('Y-m-d H:i:s', $existing['expiration']);

            if ($expiration && $expiration > $now) {
                // Token encore valide → on le renvoie, pas besoin de refresh
                return $existing['token'];
            }

            // Token expiré → on le supprime
            $deleteQuery = <<<SQL
            DELETE FROM csrf 
            WHERE idsecure = :secureid::uuid 
            AND formname = :formname
            SQL;
            $deleteStmt = $this->connect->prepare($deleteQuery);
            $deleteStmt->bindValue(':secureid',  $secureId, PDO::PARAM_STR);
            $deleteStmt->bindValue(':formname',  $this->formName, PDO::PARAM_STR);
            $deleteStmt->execute();
        }

        // Pas de token ou token expiré supprimé → on en génère un nouveau
        return $this->generateToken();
    }


public function getValidToken(): array
{
    // Cas 1 : Token non fourni → on tente de supprimer et générer un nouveau
    if (empty($this->csrf)) {
        $csrf = $this->refreshCsrf(); // Nettoie l’ancien si existe
        return [$this->formName => is_string($csrf) ? $csrf : null];
    }

    // Cas 2 : Token fourni, mais s’il est invalide ou expiré → on fait pareil
    if (!$this->checkCsrf()) {
        $csrf = $this->refreshCsrf();
        return [$this->formName => is_string($csrf) ? $csrf : null];
    }

    // Cas 3 : Token valide → on en régénère un par sécurité (rotation)
    $csrf = $this->refreshCsrf();
    return [$this->formName => is_string($csrf) ? $csrf : null];
}

}
