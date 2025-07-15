<?php

namespace App\traits;

/**
 * Trait Clean
 * Gère le nettoyage des tokens ciblé via fonction Postgres.
 * Elle possède aussi deux fonctions pour alléger Login qui fait appel a des fonctions postgres pour remise à 0 ou incrémentation de attempts
 */

use PDO;

trait Clean
{

    use Link;

    protected function cleanCibledToken($loginId)
    {
        $statement = $this->connect->prepare("SELECT cleancibledtoken(:loginid)");
        $statement->bindValue(':loginid', $loginId, PDO::PARAM_STR);
        $statement->execute();
    }

    public function incrementAttempts(string $loginid): void
    {
        $query = 'SELECT incrementattempts(:loginid)';
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':loginid', $loginid, PDO::PARAM_STR);
        $statement->execute();
    }

    public function resetAttempts(string $loginid): void
    {
        $query = 'SELECT resetattempts(:loginid)';
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':loginid', $loginid, PDO::PARAM_STR);
        $statement->execute();
    }
}
