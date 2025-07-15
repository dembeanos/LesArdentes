<?php
namespace App\controllers\doctor;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use PDO;

final class Credit {

    use Link;
    use Dto;

    private string $doctorId;
    private string $appointmentId;
    private float $credit;
    private string $paymentMethod;
    private string $status;

    public function __construct(array $var) { $this->constructVar($var);}
    

    public function pushCredit(): array {

        $query = <<<SQL
            INSERT INTO credits 
            (iddoctor, idappointment, credit, debit, paymentmethod, status, paymentdate)
            VALUES
            (:iddoctor, :appointmentId, :credit, 0, :paymentmethod, :status, NOW())
        SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':iddoctor', $this->doctorId, PDO::PARAM_STR);
        $statement->bindValue(':appointmentId', $this->appointmentId, PDO::PARAM_STR);
        $statement->bindValue(':credit', $this->credit, PDO::PARAM_INT);
        $statement->bindValue(':paymentmethod', 'CreditCard', PDO::PARAM_STR);
        $statement->bindValue(':status', 'paid', PDO::PARAM_STR);
        if ($statement->execute()) {

            Notifier::console('Credit ajoutée avec succès');
            return [];
        } else {
            Notifier::console('Erreur lors de l\'ajout du Credit');
            Notifier::log('Erreur dans Credits methode pushCredit', 'error');
        }
        return [];
    }
}

