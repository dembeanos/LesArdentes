<?php
declare (strict_types = 1);

namespace App\database;
use App\config\Config;
use PDO;
use PDOException;

final class Database {

    private PDO|null $pdo = null;
    private string $host;
    private string $port;
    private string $dbName;
    private string $dbUser;
    private string $dbPass;
    private bool $isConnected = false;

    public function __construct(){
        $this->host = Config::getPgsqlHost();
        $this->port = Config::getPgsqlPort();
        $this->dbName = Config::getPgsqlDbName();
        $this->dbUser = Config::getPgsqlDbUser();
        $this->dbPass = Config::getPgsqlPswd();

        $this->connect();

    }

    private function connect():void {
        try{
        $this->pdo = new PDO(
            "pgsql:host={$this->host};port={$this->port};dbname={$this->dbName}",
            $this->dbUser,
            $this->dbPass,
            array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE =>PDO::ERRMODE_EXCEPTION
            )
            );
            $this->isConnected=true;
    } catch(PDOException $e){
        throw new PDOException("Erreur de connexion : " . $e->getMessage());
    }
}

    

    public function getPdo(): ? PDO {
        return $this->pdo;
    }

    public function isConnected():bool {
        return $this->isConnected;
    }

    public function disconnect():void {
        $this->pdo = null;
        $this->isConnected = false;
    }

     public function __destruct() {
        $this->disconnect();
    }

}
?>