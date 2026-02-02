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
        $questions = $this->getQuestionsInternal();
        echo json_encode($questions, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getWikipediaFlag($title) {
        $url = "https://it.wikipedia.org/w/api.php?action=query&prop=pageimages&format=json&piprop=original&titles=" . urlencode($title);
        try {
            $context = stream_context_create([
                'http' => ['header' => "User-Agent: SiParteApp/1.0 (contact: info@siparte.it)\r\n"]
            ]);
            $response = file_get_contents($url, false, $context);
            $data = json_decode($response, true);
            $pages = $data['query']['pages'] ?? [];
            foreach ($pages as $page) {
                if (isset($page['original']['source'])) {
                    return $page['original']['source'];
                }
            }
        } catch (Exception $e) {
            error_log("Wikipedia API error: " . $e->getMessage());
        }
        return null;
    }

    public function selectPaese($data) {
        header('Content-Type: application/json; charset=utf-8');
        $answers = $data['answers'] ?? [];

        if (!$this->db) {
            echo json_encode([
                'success' => true,
                'paese_selezionato' => [
                    'id' => 1, 
                    'nome' => 'Italia (Offline)', 
                    'descrizione' => 'DB non connesso.',
                    'flag' => 'https://upload.wikimedia.org/wikipedia/commons/0/03/Flag_of_Italy.svg'
                ]
            ]);
            exit;
        }

        try {
            // Calcolo punteggi categorie basato sulle prime 3 risposte
            $categoryScores = ['beach' => 0, 'mountain' => 0, 'city' => 0, 'culture' => 0];
            $questions = $this->getQuestionsInternal();
            
            foreach ($answers as $qIdx => $ansIdx) {
                if (isset($questions[$qIdx]['answers'][$ansIdx]['scores'])) {
                    foreach ($questions[$qIdx]['answers'][$ansIdx]['scores'] as $cat => $score) {
                        if (isset($categoryScores[$cat])) {
                            $categoryScores[$cat] += $score;
                        }
                    }
                }
            }

            // Recupera tutti i paesi e calcola il matching
            $result = $this->db->query("SELECT id, nome, descrizione, categorie_suggerite FROM paesi");
            $paesi = [];
            while ($row = $result->fetch_assoc()) {
                $suggerite = json_decode($row['categorie_suggerite'], true) ?: [];
                $matchScore = 0;
                foreach ($suggerite as $cat) {
                    if (isset($categoryScores[$cat])) {
                        $matchScore += $categoryScores[$cat];
                    }
                }
                $row['match_score'] = $matchScore;
                $paesi[] = $row;
            }

            // Ordina per match_score e prendi il migliore (o uno dei migliori a random)
            usort($paesi, function($a, $b) { return $b['match_score'] <=> $a['match_score']; });
            
            // Prende tra i primi 3 per varietà
            $topPaesi = array_slice($paesi, 0, 3);
            $paese = $topPaesi[array_rand($topPaesi)];
            
            $paese['flag'] = $this->getWikipediaFlag($paese['nome']);

            echo json_encode(['success' => true, 'paese_selezionato' => $paese]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    private function getQuestionsInternal() {
        return [
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
    }

    public function submitQuiz($data) {
        header('Content-Type: application/json; charset=utf-8');
        $paeseId = $data['paese_id'] ?? null;
        $answers = $data['answers'] ?? [];

        if (!$this->db || !$paeseId) {
            echo json_encode([
                'success' => true,
                'recommended_destination' => [
                    'name' => 'Roma',
                    'description' => 'La città eterna ti aspetta per un viaggio indimenticabile!',
                    'flag' => 'https://upload.wikimedia.org/wikipedia/commons/0/03/Flag_of_Italy.svg'
                ]
            ]);
            exit;
        }

        try {
            // Calcolo punteggi categorie basato su TUTTE le risposte
            $categoryScores = ['beach' => 0, 'mountain' => 0, 'city' => 0, 'culture' => 0];
            $questions = $this->getQuestionsInternal();
            
            foreach ($answers as $qIdx => $ansIdx) {
                if (isset($questions[$qIdx]['answers'][$ansIdx]['scores'])) {
                    foreach ($questions[$qIdx]['answers'][$ansIdx]['scores'] as $cat => $score) {
                        if (isset($categoryScores[$cat])) {
                            $categoryScores[$cat] += $score;
                        }
                    }
                }
            }

            // Recupera le città del paese e calcola il matching
            $stmt = $this->db->prepare("SELECT nome, descrizione, categoria_viaggio, fascia_budget_base FROM citta WHERE id_paese = ?");
            $stmt->bind_param("i", $paeseId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cittaList = [];
            while ($row = $result->fetch_assoc()) {
                $matchScore = 0;
                $cat = $row['categoria_viaggio'];
                if (isset($categoryScores[$cat])) {
                    $matchScore += $categoryScores[$cat];
                }
                
                // Bonus budget (semplificato)
                $budgetAns = $answers[2]; // Domanda 3 è il budget
                $budgetMap = [0 => 500, 1 => 1000, 2 => 1500, 3 => 2000];
                $userBudget = $budgetMap[$budgetAns] ?? 1000;
                
                if ($row['fascia_budget_base'] <= $userBudget) {
                    $matchScore += 2;
                }

                $row['match_score'] = $matchScore;
                $cittaList[] = $row;
            }

            // Ordina e scegli
            usort($cittaList, function($a, $b) { return $b['match_score'] <=> $a['match_score']; });
            
            $bestCitta = $cittaList[0] ?? ['nome' => 'Capitale', 'descrizione' => 'Esplora il cuore del paese!'];
            
            $response = [
                'name' => $bestCitta['nome'],
                'description' => $bestCitta['descrizione'],
                'flag' => $this->getWikipediaFlag($bestCitta['nome'])
            ];

            echo json_encode([
                'success' => true,
                'recommended_destination' => $response
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}