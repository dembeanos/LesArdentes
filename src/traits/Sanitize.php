<?php
namespace App\traits;
/**
 * Trait Sanitize
 * 
 * Ce trait permet d’appliquer `htmlspecialchars()` sur les champs sensibles à l’injection HTML.
 * 
 * Sécurité en entrée : il est appelé dès l’entrée dans le routeur, en complément du Validator et du Dto.
 * Sécurité en sortie : utilisé dans les classes métiers lors de la restitution des données vers le front.
 * 
 * Les champs à encoder/décoder sont listés explicitement via `sanitizableFields()`.
 * 
 * Avantage : évite les injections XSS tout en centralisant la logique d’échappement HTML.
 */
trait Sanitize {


    private function htmlCharsEncode(mixed $var): string
    {
        return htmlspecialchars((string) $var, ENT_QUOTES, 'UTF-8');
    }

    private function htmlCharsDecode(mixed $var): string
    {
        return html_entity_decode((string) $var, ENT_QUOTES, 'UTF-8');
    }

    private function sanitize(string $key, $value): string|false
    {
        return in_array($key, $this->sanitizableFields(), true)
            ? $this->htmlCharsEncode($value)
            : false;
    }

    private function sanitizeDecode(string $key, $value): string|false
    {
        return in_array($key, $this->sanitizableFields(), true)
            ? $this->htmlCharsDecode($value)
            : false;
    }

    private function sanitizableFields(): array
    {
        return [
            // Texte court
            'lastName', 'firstName', 'gender', 'bloodGroup',

            // Texte long
            'diagnosis', 'prescription', 'notes', 'allergy',
            'medicalHistory', 'object', 'content','comment', 'reason',

            // Adresse
            'address',
            'email',
            'phone',
        ];
    }

    public function varSanitize(array &$data): void
    {
        foreach ($data as $key => $value) {
            $encoded = $this->sanitize($key, $value);
            if ($encoded !== false) {
                $data[$key] = $encoded;
            }
        }
    }

    public function varSanitizeDecode(array &$data): void
    {
        foreach ($data as $key => $value) {
            $decoded = $this->sanitizeDecode($key, $value);
            if ($decoded !== false) {
                $data[$key] = $decoded;
            }
        }
    }
}
