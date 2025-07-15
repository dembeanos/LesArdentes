<?php
declare(strict_types=1);

namespace App\auth;

use App\traits\Clean;
use App\config\Config;
use App\traits\Link;
use App\core\Notifier;
use PDO;

final class Session
{
    use Link;
    use Clean;

    private string $loginId;
    private string $role;

    // Durée d’inactivité max en secondes (ici 30 minutes)
    private const TIMEOUT_IN_SECONDS = 1800;

    public function __construct(string $loginId, string $role)
    {
        $this->loginId = trim($loginId);
        $this->role    = trim($role);
    }

    private function verifyLoginId(): bool
    {
        if ($this->loginId === '') {
            Notifier::Popup('Echec de connexion');
            Notifier::log("Session : loginId vide.", 'error');
            return false;
        }
        return true;
    }

    private function verifyRole(): bool
    {
        if (!in_array($this->role, ['doctor', 'patient', 'secretary'], true)) {
            Notifier::Popup('Echec de connexion');
            Notifier::log("Session : rôle invalide ({$this->role}).", 'error');
            return false;
        }
        return true;
    }

    private function getIdType(): ?array
    {
        return match ($this->role) {
            'doctor'    => ['idType' => 'doctorid',   'table' => 'doctors'],
            'patient'   => ['idType' => 'patientid',  'table' => 'patients'],
            'secretary' => ['idType' => 'secretaryid','table' => 'secretaries'],
            default     => null,
        };
    }

    private function getUserId(): array|false
    {
        // 1) Vérifier loginId et rôle
        if (!$this->verifyLoginId() || !$this->verifyRole()) {
            return false;
        }

        $data = $this->getIdType();
        if ($data === null) {
            Notifier::Popup('Echec de connexion');
            Notifier::log("Session : getIdType() retourne null pour rôle '{$this->role}'.", 'error');
            return false;
        }

        $idType = $data['idType'];
        $table  = $data['table'];
        $key = Config::getCrypto();

        if ($this->role === 'patient') {
        $query =<<<SQL
            SELECT t.$idType, pgp_sym_decrypt(t.lastname::bytea, :key) AS lastname
            FROM $table t
            JOIN logins l ON t.idlogin = l.loginid
            WHERE l.loginid = :loginId
            LIMIT 1
        SQL;
    } else {
        $query =<<<SQL
            SELECT t.$idType, t.lastname
            FROM $table t
            JOIN logins l ON t.idlogin = l.loginid
            WHERE l.loginid = :loginId
            LIMIT 1
        SQL;
    }
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':loginId', $this->loginId, PDO::PARAM_STR);
        if ($this->role === 'patient') {
            $statement->bindValue(':key', $key, PDO::PARAM_STR);
        }

        if (!$statement->execute()) {
            Notifier::Popup('Echec de connexion');
            Notifier::log("Session : getUserId() - échec execute()", 'fatal');
            return false;
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            Notifier::Popup('Echec de connexion');
            Notifier::log("Session : getUserId() - Utilisateur introuvable pour loginId '{$this->loginId}'.", 'fatal');
            return false;
        }

        return $row;
    }

    private function isExpired(): bool
    {
        if (empty($_SESSION['lastActivity'])) {
            // Pas encore de timestamp enregistré, donc pas expirée
            return false;
        }
        return ((time() - (int) $_SESSION['lastActivity']) > self::TIMEOUT_IN_SECONDS);
    }

public function startSession(): bool|array
{
    // 1) Si la session n’est pas encore démarrée, on configure le cookie puis on la démarre
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,      // expire à la fermeture du navigateur
            'path'     => '/',
            'secure'   => false,  // à true en production HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }

    // 2) Si la session est déjà ouverte, on vérifie le timeout d’inactivité
    if (isset($_SESSION['loginId'])) {
        if ($this->isExpired()) {
            $this->destroy();
            Notifier::Popup('Session expirée après inactivité, veuillez vous reconnecter.');
            return false;
        }
        $_SESSION['lastActivity'] = time();
        return true;
    }

    // 3) Nouvelle connexion
    $userId = $this->getUserId();
    if ($userId === false) {
        return false;
    }

    // 4) Anti-fixation de session
    session_regenerate_id(true);

    $userInfo = $this->getUserId();

    $data = $this->getIdType();
    $idType = $data['idType'];

    // 5) Remplissage de la session
    $_SESSION['userId']       = $userInfo[$idType];
    $_SESSION['lastName']       = $userInfo['lastname'];
    $_SESSION['loginId']      = $this->loginId;
    $_SESSION['role']         = $this->role;
    $_SESSION['lastActivity'] = time();

    Notifier::log("Session démarrée pour loginId '{$this->loginId}'.", 'info');
    Notifier::console('Connexion réussie !');
    return true;
}


    public function destroy(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // 1) Vider tout $_SESSION
        $_SESSION = [];
        session_unset();
        session_destroy();

        // 2) Nettoyer les tokens CSRF ciblés (méthode du trait Clean)
        $this->cleanCibledToken($this->loginId);

        // 3) Supprimer le cookie côté navigateur
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain']   ?? '',
                $params['secure']   ?? false,
                $params['httponly'] ?? false
            );
        }

        Notifier::log("Session détruite pour loginId '{$this->loginId}'.", 'info');
        Notifier::popup('Vous avez été déconnecté.');
        Notifier::redirect('/Ardentes/public/index.php');
        return true;
    }
}

?> 