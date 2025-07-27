<?php
declare (strict_types = 1 );
namespace App\config;
use Dotenv\Dotenv;
use \Exception;

final class Config {

    private static ?array $env = null;

   private static function loadEnv(): void {
    if (self::$env === null) {
        $envPath = __DIR__ . '/../../.env';
        if (file_exists($envPath)) {
            // Si on a un .env local, on le charge (dev/local)
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();
            self::$env = $_ENV;
        } else {
            // Pas de .env (prod Render), on prend les variables d'environnement système
            self::$env = [];
            $keys = ['CRYPTOKEY', 'PGSQLDBUSER', 'PGSQLPSWD', 'PGSQLHOST', 'PGSQLPORT', 'PGSQLDBNAME'];
            foreach ($keys as $key) {
                $value = getenv($key);
                if ($value === false) {
                    $value = $_ENV[$key] ?? '';
                }
                self::$env[$key] = $value;
            }
        }
    }
}

    public static function getCrypto(): string {
        self::loadEnv();
        $cryptoKey = self::$env['CRYPTOKEY'] ?? '';
        if (!$cryptoKey) {
            throw new Exception("CRYPTOKEY manquant ou introuvable");
        }
        return $cryptoKey;
    }

    public static function getPgsqlDbUser(): string {
        self::loadEnv();
        $pgsqlDbUser = self::$env['PGSQLDBUSER'] ?? '';
        if (!$pgsqlDbUser) {
            throw new Exception("PGSQLDBUSER manquant ou introuvable");
        }
        return $pgsqlDbUser;
    }

    public static function getPgsqlPswd(): string {
        self::loadEnv();
        $pgsqlPswd = self::$env['PGSQLPSWD'] ?? '';
        if (!$pgsqlPswd) {
            throw new Exception("PGSQLPSWD manquant ou introuvable");
        }
        return $pgsqlPswd;
    }
    public static function getPgsqlHost(): string {
        self::loadEnv();
        $pgsqlHost = self::$env['PGSQLHOST'] ?? '';
        if (!$pgsqlHost) {
            throw new Exception("PGSQLHOST manquant ou introuvable");
        }
        return $pgsqlHost;
    }

    public static function getPgsqlPort(): string {
        self::loadEnv();
        $pgsqlPort = self::$env['PGSQLPORT'] ?? '';
        if (!$pgsqlPort) {
            throw new Exception("PGSQLPORT manquant ou introuvable");
        }
        return $pgsqlPort;
    }
    
    public static function getPgsqlDbName(): string {
        self::loadEnv();
        $pgsqlDbName = self::$env['PGSQLDBNAME'] ?? '';
        if (!$pgsqlDbName) {
            throw new Exception("PGSQLDBNAME manquant ou introuvable");
        }
        return $pgsqlDbName;
    }
}











?>