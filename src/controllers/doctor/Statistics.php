<?php
declare (strict_types = 1 );
namespace App\controllers\doctor;

use App\traits\Dto;
use App\traits\Link;
use App\core\Notifier;
use PDO;

final class Statistics
{
    use Link;
    use Dto;

    private string $doctorId;


    public function __construct(array $var)
    {
        $this->constructVar($var);
    }


    // ========== PATIENT DU JOUR ==========
    public function getPatientToday(): array
    {

        $query = <<<SQL
                    SELECT COUNT(*) AS patientToday
                    FROM appointments 
                    WHERE DATE(appointmentdate) = CURRENT_DATE
                    AND iddoctor = :doctorId::uuid
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer la liste des patients du jour");
            Notifier::log("Erreur dans Statistics: impossible d'exécuter getPatientToday", 'error');
            return [];
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            Notifier::console('Pas de rendez-vous trouvé');
            return [];
        }

        return $result;
    }

    // ========== NOUVEAU PATIENT CE MOIS ==========
    public function getNewPatientMonth(): array
    {

        $query = <<<SQL
                    SELECT COUNT(*) AS newPatientMonth
                    FROM associations
                    WHERE date_trunc('month', linkdate) = date_trunc('month', CURRENT_DATE)
                    AND archive ='false'
                      AND iddoctor = :doctorId::uuid
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les nouveaux patients du mois");
            Notifier::log("Erreur dans Statistics SQL getNewPatientMonth", 'error');
            return [];
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    public function getMoneyMonth(): array
    {

        $query = <<<SQL
                    SELECT SUM(credit) AS moneyMonth
                    FROM credits
                    WHERE date_trunc('month', paymentdate) = date_trunc('month', CURRENT_DATE)
                      AND iddoctor = :doctorId::uuid
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les credits du mois");
            Notifier::log("Erreur dans Statistics SQL getMoneyMonth", 'error');
            return [];
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ['moneyMonth' => $result['moneyMonth'] ?? 0];

    }

        public function getAttendance(): array
    {

        $query = <<<SQL
                    SELECT COUNT(*) AS attendance
                    FROM appointments
                    WHERE DATE(appointmentDate) = CURRENT_DATE
                      AND status = 'passed'
                      AND iddoctor = :doctorId::uuid
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les rendez-vous du mois");
            Notifier::log("Erreur dans Statistics SQL getAppointmentMonth", 'error');
            return [];
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    // ========== RDV CE MOIS ==========
    public function getAppointmentMonth(): array
    {
        $query = <<<SQL
                    SELECT COUNT(*) AS total
                    FROM consultations
                    WHERE date_trunc('month', consultation_date) = date_trunc('month', CURRENT_DATE)
                      AND iddoctor = :doctorId::uuid
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les rendez-vous du mois");
            Notifier::log("Erreur dans Statistics SQL getAppointmentMonth", 'error');
            return [];
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    // ========== TAUX DE PRÉSENCE ==========
    public function getAttendanceRate(): array|float
    {

        $queryTotal   = <<<SQL
                            SELECT COUNT(*)
                            FROM appointments 
                            WHERE iddoctor = :doctorId 
                            AND status != 'cancelled'
                            SQL;

        $statementTotal = $this->connect->prepare($queryTotal);
        $statementTotal->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
        if (!$statementTotal->execute()) {
            Notifier::console("Impossible de calculer le taux de présence (total)");
            Notifier::log("Erreur dans Statistics méthode getAttendanceRate total", 'error');
            return [];
        }
        $total = (int)$statementTotal->fetchColumn();

        if ($total === 0) {
            return 0;
        }

        $queryPresent = <<<SQL
                            SELECT COUNT(*) 
                            FROM appointments 
                            WHERE iddoctor = :doctorId 
                            AND status = 'ended'
                            SQL;

        $statementPresent = $this->connect->prepare($queryPresent);
        $statementPresent->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);
        if (!$statementPresent->execute()) {
            Notifier::console("Impossible de calculer le taux de présence (présents)");
            Notifier::log("Erreur dans Statistics méthode getAttendanceRate present", 'error');
            return [];
        }
        $present = (int)$statementPresent->fetchColumn();

        return ['attendance' => round(($present / $total) * 100, 2)];
    }

    // ========== STAT. CRÉDIT MÉDECIN (6 MOIS) ==========

    public function getDoctorCreditStat(): array
    {

        $query = <<<SQL
                    WITH months AS (
                        SELECT generate_series(
                            date_trunc('month', CURRENT_DATE) - INTERVAL '5 months',
                            date_trunc('month', CURRENT_DATE),
                            INTERVAL '1 month'
                        ) AS month
                    )
                    SELECT
                        months.month,
                        COALESCE(SUM(credits.credit), 0) AS total
                    FROM months
                    LEFT JOIN credits
                        ON date_trunc('month', credits.paymentdate) = months.month
                        AND credits.iddoctor = :doctorId::uuid
                    GROUP BY months.month
                    ORDER BY months.month ASC
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les stats de crédit médecin");
            Notifier::log("Erreur dans Statistics méthode getDoctorCreditStat", 'error');
            return [];
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== STAT. CRÉDIT CABINET (6 MOIS) ==========

    public function getOfficeCreditStat(): array
    {
        $query = <<<SQL
                                        WITH months AS (
                        SELECT generate_series(
                            date_trunc('month', CURRENT_DATE) - INTERVAL '5 months',
                            date_trunc('month', CURRENT_DATE),
                            INTERVAL '1 month'
                        ) AS month
                    )
                    SELECT 
                        months.month,
                        COALESCE(SUM(credits.credit), 0) AS total
                    FROM months
                    LEFT JOIN credits
                        ON date_trunc('month', credits.paymentdate) = months.month
                        AND credits.status = 'paid'
                    GROUP BY months.month
                    ORDER BY months.month ASC;
                        
                    SQL;

        $statement = $this->connect->prepare($query);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les stats de crédit cabinet");
            Notifier::log("Erreur dans Statistics méthode getOfficeCreditStat", 'error');
            return [];
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== STAT. RDV (6 MOIS) ==========
    public function getAppointmentStat(): array
    {
        $query = <<<SQL
                    WITH months AS (
                        SELECT generate_series(
                            date_trunc('month', CURRENT_DATE) - INTERVAL '5 months',
                            date_trunc('month', CURRENT_DATE),
                            INTERVAL '1 month'
                        ) AS month
                    )
                    SELECT 
                        months.month, 
                        COALESCE(COUNT(appointments.appointmentdate), 0) AS total
                    FROM months
                    LEFT JOIN appointments 
                        ON date_trunc('month', appointments.appointmentdate) = months.month
                        AND appointments.iddoctor = :doctorId::uuid
                        AND appointments.status = 'ended'
                        AND appointments.appointmentdate >= (CURRENT_DATE - INTERVAL '6 months')
                    GROUP BY months.month
                    ORDER BY months.month ASC;
                    SQL;

        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            Notifier::console("Impossible de récupérer les stats de rendez-vous");
            Notifier::log("Erreur dans Statistics méthode getAppointmentStat", 'error');
            return [];
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // ========== STAT. NET PATIENT (6 MOIS) ==========
    public function getDoctorPatientStat(): array
    {
        $query = <<<SQL
                    WITH months AS (
                        SELECT generate_series(
                            date_trunc('month', CURRENT_DATE) - INTERVAL '5 months',
                            date_trunc('month', CURRENT_DATE),
                            INTERVAL '1 month'
                        ) AS month
                    ),
                    added AS (
                        SELECT date_trunc('month', linkdate) AS month, COUNT(*) AS added
                        FROM associations
                        WHERE iddoctor = :doctorId::uuid
                          AND linkdate >= CURRENT_DATE - INTERVAL '6 months'
                        GROUP BY month
                    ),
                    archived AS (
                        SELECT date_trunc('month', archiveddate) AS month, COUNT(*) AS archived
                        FROM associations
                        WHERE iddoctor = :doctorId::uuid
                          AND archiveddate IS NOT NULL
                          AND archiveddate >= CURRENT_DATE - INTERVAL '6 months'
                        GROUP BY month
                    )
                    SELECT 
                        months.month,
                        COALESCE(added.added, 0) - COALESCE(archived.archived, 0) AS net
                    FROM months
                    LEFT JOIN added ON added.month = months.month
                    LEFT JOIN archived ON archived.month = months.month
                    ORDER BY months.month ASC;
                    SQL;

        $statement = $this->connect->prepare($query);
        $statement->bindValue(':doctorId', $this->doctorId, PDO::PARAM_STR);

        if (!$statement->execute()) {
            Notifier::console("Impossible de récupérer les stats net patient");
            Notifier::log("Erreur dans Statistics méthode getDoctorPatientStat", 'error');
            return [];
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
}
