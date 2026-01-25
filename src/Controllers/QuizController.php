<?php
namespace SiParte\Quiz\Controllers;

use SiParte\Quiz\Database\Connection;
use Exception;

class QuizController {
    private $db;

    public function __construct() {
        try {
            $this->db = Connection::getInstance();
        } catch (Exception $e) {
            error_log("Errore connessione nel Controller: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function getQuestions() {
        header('Content-Type: application/json; charset=utf-8');

        // Domande Hardcoded per garantire il funzionamento anche senza DB
        $questions = [
            [
                'id' => 1,
                'question' => 'Quale clima preferisci?',
                'answers' => [
                    ['text' => 'Caldo e soleggiato', 'scores' => ['beach' => 3]],
                    ['text' => 'Fresco e montuoso', 'scores' => ['mountain' => 3]],
                    ['text' => 'Temperato', 'scores' => ['city' => 3]],
                    ['text' => 'Freddo', 'scores' => ['mountain' => 1]]
                ]
            ],
            [
                'id' => 2,
                'question' => 'Cosa cerchi in un viaggio?',
                'answers' => [
                    ['text' => 'Relax totale', 'scores' => ['beach' => 3]],
                    ['text' => 'Avventura e sport', 'scores' => ['mountain' => 3]],
                    ['text' => 'Musei e shopping', 'scores' => ['city' => 3]],
                    ['text' => 'Natura', 'scores' => ['mountain' => 2]]
                ]
            ],
            [
                'id' => 3,
                'question' => 'Budget per persona?',
                'answers' => [
                    ['text' => 'Economico', 'scores' => ['city' => 2]],
                    ['text' => 'Medio', 'scores' => ['beach' => 2]],
                    ['text' => 'Lusso', 'scores' => ['beach' => 3, 'city' => 3]]
                ]
            ],
            [
                'id' => 4,
                'question' => 'Che tipo di alloggio preferisci?',
                'answers' => [
                    ['text' => 'Hotel di lusso', 'scores' => ['beach' => 3]],
                    ['text' => 'B&B caratteristico', 'scores' => ['city' => 2]],
                    ['text' => 'Rifugio o Campeggio', 'scores' => ['mountain' => 3]]
                ]
            ]
        ];

        echo json_encode($questions, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function selectPaese($data) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->db) {
            echo json_encode([
                'success' => true,
                'paese_selezionato' => ['id' => 1, 'nome' => 'Italia (Offline)', 'descrizione' => 'DB non connesso.']
            ]);
            exit;
        }

        try {
            $result = $this->db->query("SELECT id, nome, descrizione FROM paesi ORDER BY RAND() LIMIT 1");
            $paese = $result ? $result->fetch_assoc() : null;
            echo json_encode(['success' => true, 'paese_selezionato' => $paese]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function submitQuiz($data) {
        header('Content-Type: application/json; charset=utf-8');
        $paeseId = $data['paese_id'] ?? null;

        if (!$this->db || !$paeseId) {
            echo json_encode([
                'success' => true,
                'recommended_destination' => [
                    'name' => 'Roma',
                    'description' => 'La città eterna ti aspetta per un viaggio indimenticabile!'
                ]
            ]);
            exit;
        }

        try {
            $stmt = $this->db->prepare("SELECT nome as name, descrizione as description FROM citta WHERE id_paese = ? ORDER BY RAND() LIMIT 1");
            if (!$stmt) {
                throw new Exception("Prepare fallita: " . $this->db->error);
            }

            $stmt->bind_param("i", $paeseId);
            if (!$stmt->execute()) {
                throw new Exception("Execute fallita: " . $stmt->error);
            }

            $citta = null;
            $result = $stmt->get_result();
            if ($result !== false) {
                $citta = $result->fetch_assoc();
            } else {
                // Fallback se mysqlnd non è disponibile
                $stmt->bind_result($name, $description);
                if ($stmt->fetch()) {
                    $citta = ['name' => $name, 'description' => $description];
                }
            }
            echo json_encode([
                'success' => true,
                'recommended_destination' => $citta ?: ['name' => 'Capitale', 'description' => 'Esplora il cuore del paese!']
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}