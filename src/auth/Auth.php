<?php
declare (strict_types = 1 );
namespace App\auth;

use App\auth\Session;

final class Auth
{

    // Cette méthode vérifie si la session est active, sinon redirige
    public static function checkSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['userId'], $_SESSION['role'])) {
            header('Location: /Ardentes/public/login.php');
            exit();
        }
    }


    public static function isPatientLogged(): bool
    {
        return isset($_SESSION['role'], $_SESSION['userId'])
            && $_SESSION['role'] === 'patient'
            && !empty($_SESSION['userId']);
    }

    public static function isDoctorLogged(): bool
    {
        return isset($_SESSION['role'], $_SESSION['userId'])
            && $_SESSION['role'] === 'doctor'
            && !empty($_SESSION['userId']);
    }

    public static function isSecretaryLogged(): bool
    {
        return isset($_SESSION['role'], $_SESSION['userId'])
            && $_SESSION['role'] === 'secretary'
            && !empty($_SESSION['userId']);
    }

    public static function getUserSessionInfo(): ?array
    {
        if (isset($_SESSION['userId'], $_SESSION['role'], $_SESSION['loginId'])) {
            return [
                'userId' => $_SESSION['userId'],
                'role' => $_SESSION['role'],
                'loginId' => $_SESSION['loginId'],
            ];
        }
        return null;
    }

    public static function requirePatientLogin()
    {
        self::checkSession();
        if (!self::isPatientLogged()) {
            header('Location: /Ardentes/public/login.php');
            exit();
        }
    }

    public static function requireDoctorLogin()
    {
        self::checkSession();
        if (!self::isDoctorLogged()) {
            header('Location: /Ardentes/public/login.php');
            exit();
        }
    }

    public static function requireSecretaryLogin()
    {
        self::checkSession();
        if (!self::isSecretaryLogged()) {
            header('Location: /Ardentes/public/login.php');
            exit();
        }
    }
}
