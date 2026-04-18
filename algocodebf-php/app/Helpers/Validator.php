<?php
/**
 * Classe Validator - Validation des données de formulaire
 */

class Validator
{
    private $errors = [];
    private $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Valider que le champ est requis
     * 
     * @param string $field Nom du champ
     * @param string $message Message d'erreur personnalisé
     * @return self
     */
    public function required($field, $message = null)
    {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field] = $message ?? "Le champ {$field} est requis";
        }
        return $this;
    }

    /**
     * Valider la longueur minimale
     * 
     * @param string $field Nom du champ
     * @param int $min Longueur minimale
     * @param string $message Message d'erreur personnalisé
     * @return self
     */
    public function min($field, $min, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit contenir au moins {$min} caractères";
        }
        return $this;
    }

    /**
     * Valider la longueur maximale
     * 
     * @param string $field Nom du champ
     * @param int $max Longueur maximale
     * @param string $message Message d'erreur personnalisé
     * @return self
     */
    public function max($field, $max, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? "Le champ {$field} ne doit pas dépasser {$max} caractères";
        }
        return $this;
    }

    /**
     * Valider un email
     * 
     * @param string $field Nom du champ
     * @param string $message Message d'erreur personnalisé
     * @return self
     */
    public function email($field, $message = null)
    {
        if (isset($this->data[$field]) && !Security::validateEmail($this->data[$field])) {
            $this->errors[$field] = $message ?? "L'adresse email n'est pas valide";
        }
        return $this;
    }

    /**
     * Valider que deux champs correspondent
     * 
     * @param string $field Nom du champ
     * @param string $matchField Nom du champ à comparer
     * @param string $message Message d'erreur personnalisé
     * @return self
     */
    public function match($field, $matchField, $message = null)
    {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) 
            && $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? "Les champs ne correspondent pas";
        }
        return $this;
    }

    /**
     * Vérifier si la validation a échoué
     * 
     * @return bool
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Obtenir les erreurs de validation
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Obtenir les données validées
     * 
     * @return array
     */
    public function validated()
    {
        return $this->data;
    }
}

