<?php

declare(strict_types=1);

namespace App\secure;

use App\traits\Link;
use App\traits\Clean;
use App\auth\Session;
use App\core\Notifier;
use DateTime;
use PDO;

class Secure
{

    use Link;
    use Clean;

    private string $loginId;
    private string $ip;
    private string $userAgent;

    public function __construct(string $loginId, string $ip, string $userAgent)
    {
        $this->loginId = trim($loginId);
        $this->ip = trim($ip);
        $this->userAgent = trim($userAgent);
    }

    private function verifyLogin(): bool
    {
        if ($this->loginId === '') {
            Notifier::log('Secure : loginId vide', 'critical');
            return false;
        }
        return true;
    }

    private function verifyIp(): bool
    {
        if (!filter_var($this->ip, FILTER_VALIDATE_IP)) {
            Notifier::log('Erreur dans Secure type ip non conforme', 'critical');
            return false;
        }
        return true;
    }

    private function verifyUserAgent(): bool
    {
        if ($this->userAgent === '') {
            Notifier::log('Secure : userAgent vide', 'critical');
            return false;
        }
        $this->userAgent = preg_replace('/[^\P{C}\n]+/u', '', $this->userAgent);
        $this->userAgent = substr($this->userAgent, 0, 255);

        if (empty($this->userAgent)) {
            Notifier::log('User-Agent vide ou invalide après nettoyage', 'critical');
            return false;
        }

        return true;
    }

    public function masterToken(): bool
    {

        if (!$this->verifyLogin() || !$this->verifyIp() || !$this->verifyUserAgent()) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiration = (new DateTime('+8 hours'))->format('Y-m-d H:i:s');

        $this->cleanCibledToken($this->loginId); // on purge avant insert les anciens token
        $query = <<<SQL
                    INSERT INTO secure (idlogin, ipaddress, token, useragent, expiration) 
                    VALUES (:loginId::uuid, :ip, :token, :userAgent, :expiration)
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':loginId', $this->loginId, PDO::PARAM_STR);
        $statement->bindValue(':ip', $this->ip, PDO::PARAM_STR);
        $statement->bindValue(':token', $token, PDO::PARAM_STR);
        $statement->bindValue(':expiration', $expiration, PDO::PARAM_STR);
        $statement->bindValue(':userAgent', $this->userAgent, PDO::PARAM_STR);
        if (!$statement->execute()) {
            Notifier::log("Erreur dans Secure Échec de l'insertion du master token", 'critical');
            Notifier::console('Erreur système lors de la création du token.');
            Notifier::input("Erreur de connexion", 'error', 'login');
            return false;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['master'] = $token;
        }


        Notifier::console('Master token généré avec succès.');
        return true;
    }

    public function checkMasterToken(): false|string
    {
        if (!$this->verifyLogin() || !$this->verifyIp() || !$this->verifyUserAgent()) {
            return false;
        }

        $query = <<<SQL
                SELECT secureid, idlogin, ipaddress, token, expiration, useragent
                FROM secure
                WHERE idlogin = :loginId::uuid
                ORDER BY expiration DESC
                LIMIT 1
                SQL;


        $statement = $this->connect->prepare($query);
        $statement->bindValue(':loginId', $this->loginId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            $session = new Session((string) $this->loginId, '');
            $session->destroy();
            Notifier::log("Erreur dans Secure Échec du check du master token", 'critical');
            Notifier::console('Erreur système lors de la verification du token.');
            Notifier::popup("Erreur de connexion, veuillez vous reconnecter");
            return false;
        }

        $masterToken = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$masterToken) {
            $session = new Session((string) $this->loginId, '');
            $session->destroy();
            Notifier::console('token expiré reconnexion requise.');
            Notifier::popup("Connexion expirée, veuillez vous reconnecter");

            return false;
        }

        $now = new DateTime();
        $expiration = DateTime::createFromFormat('Y-m-d H:i:s', $masterToken['expiration']);

        if (
            $masterToken['idlogin'] === $this->loginId &&
            $masterToken['ipaddress'] === $this->ip &&
            $expiration > $now &&
            $masterToken['useragent'] === $this->userAgent &&
            $masterToken['token'] === $_SESSION['master']
        ) {
            return $masterToken['secureid'];
        }

        $session = new Session((string) $this->loginId, '');
        $session->destroy();
        Notifier::popup("Votre session a été invalidée pour des raisons de sécurité.");
        Notifier::console("MasterToken invalide, session détruite.");
        Notifier::log("MasterToken invalide dans checkMasterToken", 'critical');

        return false;
    }
}
