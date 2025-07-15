<?php

namespace App\traits;

use App\auth\Csrf;



trait SecureInfo {


    private function getIp(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return filter_var($ip ?? '', FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }

    private function getUserAgent(): string {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userAgent = substr($userAgent, 0, 255);
        return htmlspecialchars($userAgent, ENT_QUOTES, 'UTF-8');
    }


 private function checkCsrf(string $loginId, string $formName, string $csrf): bool|array
{
    $check = new Csrf([
  'loginId' => $loginId,
  'formName' => $formName,
  'csrf' => $csrf,
]);

    return $check->checkCsrf();
}

}


