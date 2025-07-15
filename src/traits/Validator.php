<?php
// Validator : filtre les données avant injection métier
// Il restreint aussi les noms de variable aux seules variables admises par mon système (whiteliste)
// ce syteme permet de rejeter toute variables non prévues
//désencombrer les constructeurs de classes et permet une centralisation des setters 
namespace App\traits;

use App\core\Notifier;
use DateTime;

trait Validator {

    private function clean(mixed $var): string
    {
        return trim((string) $var);
    }

    private function uuidVerifier(mixed $uuid): bool
    {
        $uuid = $this->clean($uuid);
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
            return false;
        }
        return true;
    }

    private function shortTextVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        return preg_match("/^[a-zA-ZÀ-ÿ\s\-\.'\"]+$/u", $var) === 1 && strlen($var) < 20;
    }

    private function formNameVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        return preg_match('/^[a-zA-Z0-9_\-\.]{3,50}$/', $var) === 1;
    }

    private function bloodGroupVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        return preg_match("/^(A|B|AB|O)[+-]$/i", $var) === 1 && strlen($var) <= 3;
    }

    private function longTextVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        $len = mb_strlen($var, 'UTF-8');
        if ($len > 10000) return false;
        if (preg_match('/[\[\]\{\}\x00-\x08\x0B\x0C\x0E-\x1F]/u', $var)) {
            return false;
        }
        return true;
    }

    private function addressVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        return preg_match("/^[a-zA-Z0-9À-ÿ\s,.'\-]+$/u", $var) === 1 && strlen($var) <= 100;
    }

    private function emailVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        return filter_var($var, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function passwordVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $var) === 1;
    }

    private function phoneVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        return preg_match('/^[0-9+\-\s()]{10,15}$/', $var) === 1;
    }

    private function dateVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        $formats = ['Y-m-d H:i:s', 'Y-m-d', 'H:i:s'];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $var);
            if ($date && $date->format($format) === $var) {
                return true;
            }
        }
        return false;
    }
    private function csrfVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        if ($var === '') return true;
        return preg_match('/^[0-9a-f]{64}$/i', $var) === 1;
    }
    private function limitOffsetVerifier(mixed $var): bool
    {
        return ctype_digit((string)$var) && (int)$var >= 0 && (int)$var <= 10000;
    }
    private function socialSecurityVerifier(string $var): bool
    {
        $var = $this->clean($var);

        return preg_match('/^\d{1,20}$/', $var) === 1;
    }

    private function titleVerifier(mixed $var): bool
    {
        $var = $this->clean($var);
        $len = mb_strlen($var, 'UTF-8');
        if ($len < 3 || $len > 300) return false;
        if (preg_match('/[\[\]\{\}\x00-\x08\x0B\x0C\x0E-\x1F]/u', $var)) {
            return false;
        }
        return preg_match("/^[\p{L}0-9\s\-\.’'\":;,()\/\"]+$/u", $var) === 1;
    }

    private function validate(string $key, $value)
    {
        return match ($key) {
            // UUIDs
            'loginId'  => $this->uuidVerifier($value) ? true : Notifier::log('Erreur loginId Incorrecte', 'critical') && false,
            'doctorId' => $this->uuidVerifier($value) ? true : Notifier::log('Erreur doctorId Incorrecte', 'critical') && false,
            'patientId' => $this->uuidVerifier($value) ? true : Notifier::log('Erreur patientId Incorrecte', 'critical') && false,
            'senderId' => $this->uuidVerifier($value) ? true : Notifier::log('Erreur senderId Incorrecte', 'critical') && false,
            'receiverId' => $this->uuidVerifier($value) ? true : Notifier::log('Erreur receiverId Incorrecte', 'critical') && false,
            'messageId' => $this->uuidVerifier($value) ? true : Notifier::log('Erreur messageId Incorrecte', 'critical') && false,
            'appointmentId' => $this->uuidVerifier($value) ? true : Notifier::log('Erreur appointmentId Incorrecte', 'critical') && false,
            'consultationId' => $this->uuidVerifier($value) ? true : Notifier::log('Erreur appointmentId Incorrecte', 'critical') && false,
            'secretary' => $this->shortTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long', 'error', 'secretary') && false, //sert pour sendMessage Secretary
            //Csrf
            'csrfToken' => $this->csrfVerifier($value) ? true : Notifier::log('Erreur csrf Incorrecte', 'critical') && false,
            //FormName
            'formName' => $this->formNameVerifier($value) ? true : Notifier::log('FormName invalide', 'critical') && false,
            // Texte court
            'lastName' => $this->shortTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long', 'error', 'lastName') && false,
            'type' => $this->shortTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long', 'error', 'type') && false,
            'firstName' => $this->shortTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long', 'error', 'firstName') && false,
            'gender' => $this->shortTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long', 'error', 'gender') && false,
            'term' => $this->shortTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou superieur à 3 caractères', 'error', 'userSearch') && false,
            'title' => $this->titleVerifier($value) ? true : Notifier::popup('Titre rejeté') && false,
            'status' => $this->shortTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou superieur à 3 caractères', 'error', 'status') && false,
            //Groupe Sanguin
            'bloodGroup' => $this->bloodGroupVerifier($value) ? true : Notifier::input('Caractère non autorisé ou superieur à 3 caractères', 'error', 'bloodGroup') && false,
            // Texte long
            'symptoms' => $this->longTextVerifier($value) ? true : Notifier::popup('Texte trop long ou invalide') && false,
            'diagnosis' => $this->longTextVerifier($value) ? true : Notifier::popup('Texte trop long ou invalide') && false,
            'prescription' => $this->longTextVerifier($value) ? true : Notifier::popup('Texte trop long ou invalide') && false,
            'notes' => $this->longTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 1000 caractères', 'error', 'notes') && false,
            'allergy' => $this->longTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 1000 caractères', 'error', 'allergy') && false,
            'medicalHistory' => $this->longTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 1000 caractères', 'error', 'medicalHistory') && false,
            'object' => $this->longTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 1000 caractères', 'error', 'object') && false,
            'content' => $this->longTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 1000 caractères', 'error', 'content') && false,
            'comment' => $this->longTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 1000 caractères', 'error', 'comment') && false,
            'reason'=> $this->longTextVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 1000 caractères', 'error', 'reason') && false,
            // Adresse
            'address' => $this->addressVerifier($value) ? true : Notifier::input('Caractère non autorisé ou texte trop long max= 100 caractères', 'error', 'address') && false,
            // Email
            'email' => $this->emailVerifier($value) ? true : Notifier::input('Email non valide', 'error', 'email') && false,
            // Téléphone
            'phone' => $this->phoneVerifier($value) ? true : Notifier::input('Numéro de téléphone non conforme', 'error', 'phone') && false,
            //Numéro de Sécu
            'socialSecurity' => $this->socialSecurityVerifier($value) ? true : Notifier::input('Numéro de Sécurité social non conforme', 'error', 'socialSecurity') && false,
            // Dates
            'birthDate' => $this->dateVerifier($value) ? true : Notifier::input('Date non conforme', 'error', 'birthDate') && false,
            'consultationDate' => $this->dateVerifier($value) ? true : Notifier::input('Date non conforme', 'error', 'consultationDate') && false,
            'appointmentDate' => $this->dateVerifier($value) ? true : Notifier::input('Date non conforme', 'error', 'appointmentDateTime') && false,
            // Horaires du cabinet
            'morningStart' => preg_match('/^\d{2}:\d{2}$/', $this->clean($value)) ? true : Notifier::input('Heure d\'ouverture matin invalide', 'error', 'morningStart') && false,
            'morningEnd' => preg_match('/^\d{2}:\d{2}$/', $this->clean($value)) ? true : Notifier::input('Heure de fermeture midi invalide', 'error', 'morningEnd') && false,
            'afternoonStart' => preg_match('/^\d{2}:\d{2}$/', $this->clean($value)) ? true : Notifier::input('Heure d\'ouverture après-midi invalide', 'error', 'afternoonStart') && false,
            'afternoonEnd' => preg_match('/^\d{2}:\d{2}$/', $this->clean($value)) ? true : Notifier::input('Heure de fermeture soir invalide', 'error', 'afternoonEnd') && false,
            'closedWeekdays' => is_array($value) ? true : Notifier::input('Les jours fermés doivent être une liste', 'error', 'closedWeekdays') && false,
            'closedDays' => preg_match('/^(\d{2}\/\d{2}\/\d{4})(,\s*\d{2}\/\d{2}\/\d{4})*$/', $this->clean($value)) || $value === '' ? true : Notifier::input('Format des jours fériés invalide (jj/mm/aaaa)', 'error', 'closedDays') && false,

            //Password
            'newPassword' => $this->passwordVerifier($value) ? true : Notifier::input('Le nouveau mot de passe est non conforme', 'error', 'newPassword') && false,
            'backPassword' => $this->passwordVerifier($value) ? true : Notifier::input('Ancien mot de passe non conforme', 'error', 'backPassword') && false,
            'confirmPassword' => $this->passwordVerifier($value) ? true : Notifier::input('Mot de passe non conforme', 'error', 'confirmPassword') && false,
            //Paramètres de recherches
            'limit' => $this->limitOffsetVerifier($value) ? true : Notifier::console('Valeur non valide pour limit') && false,
            'offset' => $this->limitOffsetVerifier($value) ? true : $this->consoleMessage('Valeur non valide pour offset') && false,
            'from' => $this->dateVerifier($value) ? true : Notifier::console('Intervalle de recherche rejeté') && false,
            'to' => $this->dateVerifier($value) ? true : Notifier::console('Intervalle de recherche rejeté') && false,

            //price
            'price' => is_numeric($value) && $value >= 0 && $value <= 1000 ? true : Notifier::input('Prix invalide (0-1000€)', 'error', 'price') && false,

            default => Notifier::console('Clé: ' . $key . ' inconnue ou validation non définie') && false,
        };
    }

    public function checkVar(array $data): bool
    {
        $isValid = true;
        foreach ($data as $key => $value) {
            if ($key === 'action') continue;
            if (!$this->validate($key, $value)) {
                $isValid = false;
            }
        }
        return $isValid;
    }
}
