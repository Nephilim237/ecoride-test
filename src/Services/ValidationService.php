<?php

namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Service;
use Exception;

class ValidationService extends Service
{
    public function validate_required(string $field, mixed $value): bool
    {
        if (empty($value) || trim((string)$value === '')) {
            $this->errors[$field] = "Le champ $field est obligatoire.";
            return false;
        }

        return true;
    }

    public function validate_email(string $field, $value): bool
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "L'adresse Email n'est pas valide.";
            return false;
        }

        return true;
    }

    public function validate_min($field, $value, $min): bool
    {
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field] = "Le champ $field doit contenir au moins $min caracteres.";
            return false;
        }

        return true;
    }

    public function validate_max($field, $value, $max): bool
    {
        if (!empty($value) && mb_strlen($value) > $max) {
            $this->errors[$field] = "Le champ $field doit contenir au plus $max caracteres.";
            return false;
        }

        return true;
    }

    public function validate_numeric($field, $value): bool
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "Le champs $field doit etre numeric.";
            return false;
        }

        return true;
    }

    public function validate_date($field, $value): bool
    {
        if (!empty($value)) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->errors[$field] = "Le champ $field doit etre une date valide 'YYYY-MM-DD";
                return false;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function validate_adult(string $field, $value): bool
    {
        if (!empty($value)) {
            $birthdate = new \DateTime($value);
            $today = new \DateTime();
            $age = $today->diff($birthdate)->y;

            if ($age < 18) {
                $this->errors[$field] = "Vous devez avoir au moins 18 ans pour postuler.";
                return false;
            }
        }

        return true;
    }

    public function validate_phone($field, $value): bool
    {
        if (!empty($value)) {
            $value = preg_replace('/[ .-]+/', '', $value);
            if (!preg_match('/^(0|\+33|0033)[1-9]([0-9]{2}){4}$/', $value)) {
                $this->errors[$field] = "Le numero de telephone n'est pas valide.";
                return false;
            }
        }

        return true;
    }

    public function validate_license_plate($field, $value): bool
    {
        if (!empty($value)) {
            $value = strtoupper(preg_replace('/[ .-]/', '', $value));
            if (!preg_match('/^[A-HJ-NP-TV-Z]{2}\d{3}[A-HJ-NP-TV-Z]{2}$/', $value)) {
                $this->errors[$field] = "Le numero d'immatriculation n'est pas valide.";
                return false;
            }
        }

        return true;
    }

    public function validate_in($field, $value, array $allowedValues): bool
    {
        if (!empty($value) && !in_array($value, $allowedValues)) {
            $this->errors[$field] = "La valeur du champ $field n'est pas valide.";
            return false;
        }

        return true;
    }

    public function validate_unique($field, $value, $table, $column = null, $exceptId = null): bool
    {
        if (!empty($value)) {
            $column = $column ?? $field;
            $db = \Ecoride\Ecoride\Core\Database::getInstance()->getConnection();

            $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
            $params = [$value];

            if ($exceptId) {
                $sql .= " AND user_id != ?";
                $params[] = $exceptId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ( $stmt->fetchColumn() > 0) {
                $this->errors[$field] = "Cette valeur existe deja.";
                return false;
            }
        }

        return true;
    }
}