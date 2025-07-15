<?php

namespace App\login;

use App\traits\Link;
use App\traits\Clean;
use App\secure\Secure;
use App\auth\Session;
use App\core\Notifier;
use App\traits\SecureInfo;
use PDO;
use Exception;

final class Login {

    use  Link, Clean, SecureInfo;

    private string $login;
    private string $password;

    public function __construct(string $login, string $password)
    {
        $this->login = trim($login);
        $this->password = trim($password);
    }

    protected function isValidLogin(): bool
    {
        if (strlen($this->login) < 4 || strlen($this->login) > 20) {
            Notifier::input("Le login doit contenir entre 4 et 20 caractères.", 'error', 'login');
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->login)) {
            Notifier::input("Le login ne peut contenir que des lettres, chiffres ou underscore.", 'error', 'login');
            return false;
        }
        return true;
    }

    protected function isValidPassword(): bool
    {
        if (strlen($this->password) < 8) {
            Notifier::input("Le mot de passe doit contenir au moins 8 caractères.", 'error', 'password');
            return false;
        }
        if (
            !preg_match('/[A-Z]/', $this->password) ||
            !preg_match('/[a-z]/', $this->password) ||
            !preg_match('/[0-9]/', $this->password)
        ) {
            Notifier::input("Le mot de passe doit contenir une majuscule, une minuscule et un chiffre.", 'error', 'password');
            return false;
        }
        return true;
    }

    private function checkIpRateLimit(string $ip, int $maxAttempts = 5, int $lockTime = 600): bool
    {
        $key = 'login_attempts_' . $ip;
        $attempts = \apcu_fetch($key) ?: ['count' => 0, 'first_try' => time()];

        if (time() - $attempts['first_try'] > $lockTime) {
            $attempts = ['count' => 0, 'first_try' => time()];
        }

        if ($attempts['count'] >= $maxAttempts) {
            return false;
        }

        $attempts['count']++;
        \apcu_store($key, $attempts, $lockTime);

        return true;
    }


    // Méthode principale :
    private function login(): bool
    {
        try {
            if (!$this->isValidLogin() || !$this->isValidPassword()) {
                return false;
            }

            $query = <<<SQL
                        SELECT loginid, username, password, role, attempts, status FROM logins 
                        WHERE username = :login LIMIT 1
                        SQL;

            $statement = $this->connect->prepare($query);
            $statement->bindValue(':login', $this->login, PDO::PARAM_STR);
            $statement->execute();
            $user = $statement->fetch(PDO::FETCH_ASSOC);

            $ip = $this->getIp();
            if (!$this->checkIpRateLimit($ip) && (!$user || ($user['role'] ?? '') === 'patient')) {
                sleep(10); // gros délai
                Notifier::input("Trop de tentatives, merci de patienter 10 minutes.", 'error', 'login');
                return false;
            }
            if (!$user) {
                sleep(1);
                Notifier::log('Login:Tentative de connexion Inconnu', 'warning');
                Notifier::input('Contactez votre médecin pour générer un accès.', 'error', 'login');
                return false;
            }

            if (in_array($user['role'], ['doctor', 'secretary']) && $user['attempts'] >= 5) {
                Notifier::log('Login: Tentatives excessives de connexion loginId :' . $user['loginid'] . 'username:' . $user['username'], 'critical');
                Notifier::popup('Tentatives de connexions excessives, Admin prévenu');
                return false;
            }
            if ($user['status'] === 'blocked') {
                sleep(1);
                $msg = "Votre compte est verrouillé. Contactez votre médecin.";
                $this->cleanCibledToken($user['loginid']);
                Notifier::popup($msg);
                return false;
            }
            if (!password_verify($this->password, $user['password'])) {
                sleep(1);
                $this->incrementAttempts($user['loginid']);
                
                Notifier::log("Login: mot de passe incorrect pour loginId : " . $user['loginid'], 'warning');
                Notifier::input("Identifiant ou Mot de passe incorrect.", 'error', 'login');
                return  false;
            }

            $this->resetAttempts($user['loginid']);
            $userAgent = $this->getUserAgent();

            
            $launchSession = new Session((string)$user['loginid'], (string)$user['role']);
            $result = $launchSession->startSession();
          
            if ($result === true) {
                // 1) masterToken
                $launchSecure = new Secure((string)$user['loginid'], $ip, $userAgent);
                $masterToken = $launchSecure->masterToken();
 
                // 2) redirection
                if ($masterToken === true) {

                    $redirectUrl = match ($_SESSION['role']) {
                        'doctor' => '/Ardentes/public/doctor-dashboard.php',
                        'secretary' => '/Ardentes/public/secretary-dashboard.php',
                        'patient' => '/Ardentes/public/patient-dashboard.php',
                        default => null,
                    };

                    if ($redirectUrl) {
                        Notifier::redirect($redirectUrl);
                        return true;
                    }
                }

                Notifier::popup('Echec de la connexion');
            }
        } catch (Exception $e) {
            Notifier::log("Erreur dans Login lors de la connexion : " . $e->getMessage(), 'error');
            return false;
        }
    }

    public function authenticate(): array|bool
    {
        return $this->login();
    }
}
