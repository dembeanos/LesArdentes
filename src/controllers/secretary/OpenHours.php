<?php

declare(strict_types=1);

namespace App\controllers\secretary;

use App\traits\Dto;
use App\traits\Link;
use App\traits\Sanitize;
use App\traits\Validator;
use App\core\Notifier;
use App\traits\SecureInfo;
use App\config\Config;
use PDO;

final class OpenHours
{
    use Dto;
    use Link;
    use Validator;
    use Sanitize;
    use SecureInfo;

    private string $loginId;
    private string $formName;
    private string $csrf;
    private string $morningStart;
    private string $morningEnd;
    private string $afternoonStart;
    private string $afternoonEnd;
    private array $closedWeekdays;
    private string $closedDays;

    public function __construct(array $var)
    {
        $this->constructVar($var);
    }

    public function updateOpenHours(): array
    {
        if (!$this->checkCsrf($this->loginId, $this->formName, $this->csrf)) {
            Notifier::popup("Session invalide, veuillez recharger la page.");
            Notifier::log("CSRF échoué dans updateOpenHours", 'critical');
            return ['error' => 'Requête rejetée'];
        }

        $query = <<<SQL
            INSERT INTO officehours (id, morning_start, morning_end, afternoon_start, afternoon_end, closed_weekdays, closed_days)
            VALUES ('default', :morningStart, :morningEnd, :afternoonStart, :afternoonEnd, :closedWeekdays, :closedDays)
            ON CONFLICT (id)
            DO UPDATE SET
                morning_start = EXCLUDED.morning_start,
                morning_end = EXCLUDED.morning_end,
                afternoon_start = EXCLUDED.afternoon_start,
                afternoon_end = EXCLUDED.afternoon_end,
                closed_weekdays = EXCLUDED.closed_weekdays,
                closed_days = EXCLUDED.closed_days;
        SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':morningStart', $this->morningStart, PDO::PARAM_STR);
        $statement->bindValue(':morningEnd', $this->morningEnd, PDO::PARAM_STR);
        $statement->bindValue(':afternoonStart', $this->afternoonStart, PDO::PARAM_STR);
        $statement->bindValue(':afternoonEnd', $this->afternoonEnd, PDO::PARAM_STR);
        $statement->bindValue(':closedWeekdays', json_encode($this->closedWeekdays), PDO::PARAM_STR);
        $statement->bindValue(':closedDays', $this->closedDays, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::popup("Erreur lors de l'enregistrement des horaires.");
            Notifier::log("Erreur SQL dans updateOpenHours", 'fatal');
            return ['error' => 'Erreur interne'];
        }

        Notifier::popup("Les horaires du cabinet ont bien été mis à jour.");
        return ['success' => true];
    }
}
