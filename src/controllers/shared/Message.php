<?php

declare(strict_types=1);

namespace App\controllers\shared;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use App\traits\SecureInfo;
use App\config\Config;
use App\traits\Sanitize;
use PDO;

final class Message {

    use Dto;
    use Link;
    use Sanitize;
    use SecureInfo;

    private string $doctorId;
    private string $secretaryId;
    private string $userId;
    private string $messageId;
    private string $receiverId;
    private string $senderId;
    private string $loginId;
    private string $csrf;
    private string $formName;
    private string $object;
    private string $content;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }

    public function getMessage(): array
    {
        $key = Config::getCrypto();

        $query = <<<SQL
        SELECT senderid, 
               pgp_sym_decrypt(object::bytea, :key) AS subject, 
               pgp_sym_decrypt(content::bytea, :key) AS content, 
               status,
               creationdate AS date
        FROM messages 
        WHERE receiverId = :userId AND deletedbyreceiver = FALSE
    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        $statement->bindValue(':userId', $this->userId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Impossible de récupérer les messages');
            Notifier::log('Erreur Message méthode getMessage', 'fatal');
            return [];
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->varSanitizeDecode($result);

        foreach ($result as &$message) {
            $senderId = $message['senderid'];

            if ($senderId === '00000000-0000-0000-0000-000000000001') {
                $message['sender'] = 'Secrétaire';
            } else {
                $queryGetUsername = <<<SQL
                SELECT CONCAT('Dr. ', lastname) AS name 
                FROM doctors 
                WHERE doctorid = :senderId
            SQL;

                $stmtUsername = $this->connect->prepare($queryGetUsername);
                $stmtUsername->bindValue(':senderId', $senderId, PDO::PARAM_STR);
                if ($stmtUsername->execute()) {
                    $name = $stmtUsername->fetchColumn();
                    $message['sender'] = $name ?: 'Secrétaire';
                } else {
                    $message['sender'] = 'Erreur';
                }
            }
        }

        return $result;
    }

    public function sendMessage(): array
    {
        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::log('Erreur dans Patient Manager/updatePatient: CSRF rejeté', 'critical');
            return [];
        }

        $key = Config::getCrypto();
        $query = <<<SQL
                    INSERT INTO messages (senderid, receiverid, object, content, status)
                    VALUES (:senderId, :receiverId, pgp_sym_encrypt(:object, :key), pgp_sym_encrypt(:content, :key), :status)
                    SQL;
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':senderId', $this->senderId, PDO::PARAM_STR);
        $statement->bindValue(':receiverId', $this->receiverId, PDO::PARAM_STR);
        $statement->bindValue(':key', $key, PDO::PARAM_STR);
        $statement->bindValue(':object', $this->object, PDO::PARAM_STR);
        $statement->bindValue(':content', $this->content, PDO::PARAM_STR);
        $statement->bindValue(':status', 'unread', PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup('Impossible d\'envoyer le messages');
            Notifier::log('Erreur Message méthode sendMessage Impossible d\'envoyer les messages', 'fatal');
            return [];
        }
        Notifier::popup('Votre Message à bien été remis');
        return [];
    }



    public function markIsRead(): bool
    {

        $query = <<<SQL
                    UPDATE messages SET status = 'read' WHERE messageid= :messageId
                    SQL;
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':messageId', $this->messageId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::log('Erreur Message méthode markIsRead Impossible de marquer un message comme lu', 'error');
            return false;
        }

        return true;
    }
    //---------------------------------------------------------------------------------------------------
    public function getSenderMessages()
    {
        $query = <<<SQL
        SELECT * FROM messages
        WHERE senderid = :userId AND deletedbysender = FALSE
        ORDER BY createdat DESC
    SQL;
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':userId', $this->userId, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public function deleteMessageForSender()
    {
        $query = <<<SQL
        UPDATE messages
        SET deletedbysender = TRUE
        WHERE messageid = :messageId
    SQL;
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':messageId', $this->messageId, PDO::PARAM_STR);
        $statement->execute();
    }
    public function deleteMessageForReceiver()
    {
        $query = <<<SQL
        UPDATE messages
        SET deletedbyreceiver = TRUE
        WHERE messageid = :messageId
    SQL;
        $statement = $this->connect->prepare($query);
        $statement->bindValue(':messageId', $this->messageId, PDO::PARAM_STR);
        $statement->execute();
    }




    public function getUnreadMessage(): array
    {

        $query = <<<SQL
        SELECT COUNT(*) AS unreadedMessages
        FROM messages 
        WHERE status = 'unread'
        AND receiverid = :secretaryId::uuid
    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':secretaryId', $this->secretaryId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer le nombre de message non lus");
            Notifier::log("Erreur dans Message: impossible d'exécuter getUnreadMessage", 'error');
            return [];
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            Notifier::console('Pas de message non lu trouvé');
            return [];
        }



        return ['unreadedMessages' => (int) $result['unreadedmessages']];
    }
}
