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
                'question' => 'Clima preferito?',
                'answers' => [
                    ['text' => 'Caldo e soleggiato', 'scores' => ['beach' => 3, 'city' => 1, 'culture' => 1]],
                    ['text' => 'Fresco e montuoso', 'scores' => ['mountain' => 3]],
                    ['text' => 'Mite', 'scores' => ['city' => 3, 'culture' => 3]],
                    ['text' => 'Tropicale', 'scores' => ['beach' => 3]]
                ]
            ],
            [
                'id' => 2,
                'question' => 'Cosa cerchi in un viaggio?',
                'answers' => [
                    ['text' => 'Relax totale', 'scores' => ['beach' => 3, 'mountain' => 1]],
                    ['text' => 'Avventura e sport', 'scores' => ['mountain' => 1, 'city' => 3]],
                    ['text' => 'Musei e shopping', 'scores' => ['city' => 2, 'culture' => 3]],
                    ['text' => 'Natura', 'scores' => ['mountain' => 3, 'beach' => 1]]
                ]
            ],
            [
                'id' => 3,
                'question' => 'Budget?',
                'answers' => [
                    ['text' => '0€ - 500€', 'scores' => ['city' => 2, 'culture' => 2]],
                    ['text' => '500€ - 1000€', 'scores' => ['beach' => 2, 'culture' => 2]],
                    ['text' => '1000€ - 1500€', 'scores' => ['beach' => 2, 'city' => 2, 'mountain' => 2, 'culture' => 2]],
                    ['text' => '1500€ - 2000€', 'scores' => ['mountain' => 3, 'beach' => 3, 'city' => 3, 'culture' => 3]]
                ]
            ],
            [
                'id' => 4,
                'question' => 'Cosa cerchi come alloggio?',
                'answers' => [
                    ['text' => 'Hotel di lusso', 'scores' => ['beach' => 3, 'city' => 3, 'culture' => 3, 'mountain' => 2]],
                    ['text' => 'B&B caratteristico', 'scores' => ['city' => 3, 'culture' => 3, 'beach' => 1]],
                    ['text' => 'Rifugio o Campeggio', 'scores' => ['mountain' => 3, 'beach' => 2]],
                    ['text' => 'Appartamento in centro', 'scores' => ['city' => 3, 'culture' => 3]]
                ]
            ],
            [
                'id' => 5,
                'question' => 'Quale tipo di attività preferisci?',
                'answers' => [
                    ['text' => 'Musei e sculture', 'scores' => ['culture' => 3, 'city' => 2]],
                    ['text' => 'Paesaggi mozzafiato', 'scores' => ['mountain' => 3, 'beach' => 2]],
                    ['text' => 'Tramonti indimenticabili', 'scores' => ['beach' => 3, 'mountain' => 2]],
                    ['text' => 'Vita notturna e divertimento', 'scores' => ['city' => 3, 'beach' => 1]]
                ]
            ],
            [
                'id' => 6,
                'question' => 'Quanti siete?',
                'answers' => [
                    ['text' => 'Solo', 'scores' => ['city' => 1, 'culture' => 1, 'beach' => 1, 'mountain' => 1]],
                    ['text' => 'Coppia', 'scores' => ['beach' => 1, 'mountain' => 1, 'culture' => 1, 'city' => 1]],
                    ['text' => 'Famiglia', 'scores' => ['city' => 1, 'culture' => 1, 'beach' => 1, 'mountain' => 1]],
                    ['text' => 'Gruppo di amici', 'scores' => ['beach' => 1, 'city' => 1, 'mountain' => 1, 'culture' => 1]]
                ]
            ],
            [
                'id' => 7,
                'question' => 'In che stagione vorresti partire?',
                'answers' => [
                    ['text' => 'Estate', 'scores' => ['city' => 2, 'culture' => 1, 'beach' => 3, 'mountain' => 2]],
                    ['text' => 'Autunno', 'scores' => ['city' => 3, 'culture' => 2, 'mountain' => 2]],
                    ['text' => 'Inverno', 'scores' => ['city' => 3, 'culture' => 1, 'mountain' => 3]],
                    ['text' => 'Primavera', 'scores' => ['city' => 2, 'culture' => 3, 'beach' => 1, 'mountain' => 1]]
                ]
            ],
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