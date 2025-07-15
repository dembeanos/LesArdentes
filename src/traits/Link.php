<?php

/**
 * Trait Link
 * 
 * Ce trait centralise la gestion de la connexion PDO en 
 * encapsulant l'instance Database et l'objet PDO.
 * 
 * Objectifs :
 * - Éviter de propager l'objet PDO via les constructeurs ou les setters,
 *   ce qui simplifie l'instanciation des classes utilisant la base de données.
 * - Réduire la surface d'exposition du PDO dans les classes métiers,
 *   limitant ainsi les risques d'injection ou de mauvaise utilisation.
 * - Assurer une gestion centralisée et contrôlée de la connexion,
 *   tout en facilitant la déconnexion.
 * 
 * Cette approche volontairement encapsulée permet un code plus propre,
 * maintenable et sécurisé, tout en simplifiant l'usage pour les développeurs.
 * 
 * La méthode magique __get est documentée et limitée à deux propriétés :
 * - 'connect' : pour accéder à la connexion PDO,
 * - 'disconnect' : pour fermer proprement la connexion.
 */

namespace App\traits;

use App\database\Database;
use PDO;

trait Link
{
    private ?PDO $link = null;
    private ?Database $database = null;

    public function __get(string $name)
    {                                   
        if ($name === 'connect') {
            if ($this->link === null) {
                $this->database = new Database();
                $this->link = $this->database->getPdo();
            }
            return $this->link;
        }

        if ($name === 'disconnect') {
            if ($this->database !== null && $this->database->isConnected()) {
                $this->database->disconnect();
                $this->link = null;
            }
            return null;
        }

        throw new \Exception("Propriété inconnue : $name");
    }

    protected function isConnected(): bool
    {
        return $this->database !== null && $this->database->isConnected();
    }
}
