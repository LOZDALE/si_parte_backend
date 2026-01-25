<?php
namespace SiParte\Quiz\Models;

use SiParte\Quiz\Database\Connection;

class User {
    public $id;
    public $risposte_quiz;
    public $destinazione_assegnata;
    public $tipo_viaggio;
    public $budget_finale;
    public $scelte_extra;
    public $email;

    private $db;

    public function __construct($id = null) {
        $this->db = Connection::getInstance();
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Carica un utente dal database
     */
    public function load($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $this->id = $data['id'];
            $this->risposte_quiz = json_decode($data['risposte_quiz'], true);
            $this->destinazione_assegnata = $data['destinazione_assegnata'];
            $this->tipo_viaggio = $data['tipo_viaggio'];
            $this->budget_finale = $data['budget_finale'];
            $this->scelte_extra = json_decode($data['scelte_extra'], true) ?? [];
            $this->email = $data['email'];
            return true;
        }

        return false;
    }

    /**
     * Salva o aggiorna l'utente nel database
     */
    public function save() {
        if (isset($this->id) && $this->id > 0) {
            // UPDATE
            $stmt = $this->db->prepare(
                "UPDATE users 
                 SET risposte_quiz = :risposte_quiz, 
                     destinazione_assegnata = :destinazione_assegnata, 
                     tipo_viaggio = :tipo_viaggio, 
                     budget_finale = :budget_finale, 
                     scelte_extra = :scelte_extra, 
                     email = :email
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id' => $this->id,
                ':risposte_quiz' => json_encode($this->risposte_quiz),
                ':destinazione_assegnata' => $this->destinazione_assegnata,
                ':tipo_viaggio' => $this->tipo_viaggio,
                ':budget_finale' => $this->budget_finale,
                ':scelte_extra' => json_encode($this->scelte_extra ?? []),
                ':email' => $this->email
            ]);
        } else {
            // INSERT
            $stmt = $this->db->prepare(
                "INSERT INTO users (risposte_quiz, destinazione_assegnata, tipo_viaggio, budget_finale, scelte_extra, email)
                 VALUES (:risposte_quiz, :destinazione_assegnata, :tipo_viaggio, :budget_finale, :scelte_extra, :email)"
            );
            $stmt->execute([
                ':risposte_quiz' => json_encode($this->risposte_quiz ?? []),
                ':destinazione_assegnata' => $this->destinazione_assegnata,
                ':tipo_viaggio' => $this->tipo_viaggio,
                ':budget_finale' => $this->budget_finale,
                ':scelte_extra' => json_encode($this->scelte_extra ?? []),
                ':email' => $this->email
            ]);
            $this->id = $this->db->lastInsertId();
        }
    }

    /**
     * Elimina l'utente dal database
     */
    public function delete() {
        if (isset($this->id) && $this->id > 0) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$this->id]);
        }
        return false;
    }
}
