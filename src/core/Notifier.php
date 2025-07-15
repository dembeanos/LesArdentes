<?php

namespace App\core;

use App\traits\Link;
use PDO;

class Notifier
{
    use Link;

    private static array $response = [
        'popup' => [],
        'console' => [],
        'input' => [],
        'log' => [],
        'redirect' => []
    ];

    // Ajouter un message popup
    public static function popup(string $message): void
    {
        self::$response['popup'][] = $message;
    }

    // Ajouter un message console
    public static function console(string $message): void
    {
        self::$response['console'][] = $message;
    }

    // Ajouter un message input ciblé
    public static function input(string $message, string $type, string $target): void
    {
        self::$response['input'][$target][$type][] = $message;
    }

    // Ajouter un log à stocker en base
    public static function log(string $message, string $level = 'info'): void
    {
        self::$response['log'][] = ['message' => $message, 'level' => $level];
    }

     public static function redirect(string $message): void
    {
        self::$response['redirect'][] = $message;
    }

    // Insérer les logs en base (appelée lors du flush)
    private static function saveLogsToDatabase(): void
    {
        // On instancie $connect à chaque flush
        $db = new self();

        foreach (self::$response['log'] as $log) {
            $query = "INSERT INTO logs (content, level) VALUES (:content, :level)";
            $stmt = $db->connect->prepare($query);
            $stmt->bindValue(':content', $log['message'], PDO::PARAM_STR);
            $stmt->bindValue(':level', $log['level'], PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    
    public static function flush(): array
    {
        self::saveLogsToDatabase();

        $res = self::$response;
        unset($res['log']);

        self::$response = [
            'popup' => [],
            'console' => [],
            'input' => [],
            'log' => [],
            'redirect' => []
        ];

        return $res;
    }
}
