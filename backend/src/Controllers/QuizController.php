<?php
namespace SiParte\Quiz\Controllers;

use SiParte\Quiz\Database\Connection;
use PDO;
use PDOException;
use Exception;

class QuizController {
    private ?PDO $db;

    public function __construct() {
        try {
            $this->db = Connection::getInstance();
        } catch (Exception $e) {
            error_log("Errore connessione DB nel Controller: " . $e->getMessage());
            $this->db = null;
        }
    }

    private function jsonResponse(array $data): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getQuestions(): void {
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

        $this->jsonResponse($questions);
    }

    public function selectPaese(array $data): void {
        if (!$this->db) {
            $this->jsonResponse([
                'success' => true,
                'paese_selezionato' => [
                    'id' => 1,
                    'nome' => 'Italia (Offline)',
                    'descrizione' => 'DB non connesso.'
                ]
            ]);
        }

        try {
            $stmt = $this->db->query("SELECT id, nome, descrizione FROM paesi ORDER BY RAND() LIMIT 1");
            $paese = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true,
                'paese_selezionato' => $paese ?: ['id'=>0,'nome'=>'N/A','descrizione'=>'Nessun risultato']
            ]);

        } catch (PDOException $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function submitQuiz(array $data): void {
        $paeseId = $data['paese_id'] ?? null;

        if (!$this->db || !$paeseId) {
            $this->jsonResponse([
                'success' => true,
                'recommended_destination' => [
                    'name' => 'Roma',
                    'description' => 'La città eterna ti aspetta per un viaggio indimenticabile!'
                ]
            ]);
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT nome AS name, descrizione AS description FROM citta WHERE paese_id = ? ORDER BY RAND() LIMIT 1"
            );
            $stmt->execute([$paeseId]);
            $citta = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true,
                'recommended_destination' => $citta ?: ['name' => 'Capitale', 'description' => 'Esplora il cuore del paese!']
            ]);

        } catch (PDOException $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
