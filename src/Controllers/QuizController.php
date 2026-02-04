<?php
namespace SiParte\Quiz\Controllers;

use SiParte\Quiz\Database\Connection;
use Exception;

class QuizController
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Connection::getInstance();
        } catch (Exception $e) {
            error_log("Errore connessione nel Controller: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function getQuestions()
    {
        header('Content-Type: application/json; charset=utf-8');
        $questions = $this->getQuestionsInternal();
        echo json_encode($questions, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getWikipediaFlag($title)
    {
        $url = "https://it.wikipedia.org/w/api.php?action=query&prop=pageimages&format=json&piprop=original&titles=" . rawurlencode(trim($title)) . "&redirects=1&pilicense=any";
        try {
            $context = stream_context_create([
                'http' => ['header' => "User-Agent: SiParteApp/1.0 (contact: info@siparte.it)\r\n"]
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response === false)
                return null;

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

    public function selectPaese($data)
    {
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
            $categoryScores = [
                'mare' => 0,
                'montagna' => 0,
                'città' => 0,
                'cultura' => 0,
                'divertimento' => 0,
                'natura' => 0,
                'storia' => 0,
                'cibo' => 0,
                'shopping' => 0,
                'tradizione' => 0,
                'relax' => 0,
                'tropicale' => 0
            ];
            $questions = $this->getQuestionsInternal();

            foreach ($answers as $qIdx => $ansIdx) {
                if ($qIdx >= 3)
                    break; // Solo prime 3 per il paese
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

            // Ordina per match_score
            usort($paesi, function ($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });

            // Varietà intelligente: prendiamo i paesi con punteggio vicino al migliore (entro il 20%)
            $bestScore = $paesi[0]['match_score'];
            $topPaesi = array_filter($paesi, function ($p) use ($bestScore) {
                return $bestScore > 0 ? ($p['match_score'] >= $bestScore * 0.8) : true;
            });

            // Limitiamo comunque alla top 5 per non avere troppa scelta se i punteggi sono tutti simili
            if (count($topPaesi) > 5) {
                $topPaesi = array_slice($topPaesi, 0, 5);
            }

            $paese = $topPaesi[array_rand($topPaesi)];

            $paese['flag'] = $this->getWikipediaFlag($paese['nome']);

            echo json_encode(['success' => true, 'paese_selezionato' => $paese]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    private function getQuestionsInternal()
    {
        return [
            [
                'id' => 1,
                'question' => 'Clima preferito?',
                'answers' => [
                    ['text' => 'Caldo e soleggiato', 'scores' => ['mare' => 4, 'divertimento' => 1, 'città' => 1]],
                    ['text' => 'Fresco e montuoso', 'scores' => ['montagna' => 5, 'natura' => 2]],
                    ['text' => 'Mite', 'scores' => ['città' => 4, 'cultura' => 3, 'storia' => 2]],
                    ['text' => 'Tropicale', 'scores' => ['tropicale' => 10, 'mare' => 5, 'natura' => 5, 'relax' => 5]]
                ]
            ],
            [
                'id' => 2,
                'question' => 'Cosa cerchi in un viaggio?',
                'answers' => [
                    ['text' => 'Relax totale', 'scores' => ['mare' => 4, 'montagna' => 2, 'natura' => 1]],
                    ['text' => 'Avventura e sport', 'scores' => ['montagna' => 4, 'natura' => 3, 'mare' => 1]],
                    ['text' => 'Musei e shopping', 'scores' => ['cultura' => 5, 'città' => 4, 'shopping' => 8]],
                    ['text' => 'Natura incontaminata', 'scores' => ['natura' => 5, 'montagna' => 3, 'mare' => 2]]
                ]
            ],
            [
                'id' => 3,
                'question' => 'Budget?',
                'answers' => [
                    ['text' => '0€ - 500€', 'scores' => ['cultura' => 1, 'storia' => 1]],
                    ['text' => '500€ - 1000€', 'scores' => ['mare' => 1, 'città' => 1]],
                    ['text' => '1000€ - 1500€', 'scores' => ['mare' => 2, 'montagna' => 2, 'natura' => 2]],
                    ['text' => '1500€ - 2000€+', 'scores' => ['shopping' => 5, 'tradizione' => 5]]
                ]
            ],
            [
                'id' => 4,
                'question' => 'Cosa cerchi come alloggio?',
                'answers' => [
                    ['text' => 'Hotel di lusso', 'scores' => ['mare' => 3, 'città' => 3, 'divertimento' => 2]],
                    ['text' => 'B&B caratteristico', 'scores' => ['cultura' => 3, 'storia' => 3, 'cibo' => 2]],
                    ['text' => 'Rifugio o Campeggio', 'scores' => ['montagna' => 5, 'natura' => 4]],
                    ['text' => 'Appartamento in centro', 'scores' => ['città' => 4, 'storia' => 2]]
                ]
            ],
            [
                'id' => 5,
                'question' => 'Quale attività ti ispira di più?',
                'answers' => [
                    ['text' => 'Visitare templi e siti antichi', 'scores' => ['storia' => 5, 'cultura' => 4]],
                    ['text' => 'Trekking in mezzo al verde', 'scores' => ['natura' => 5, 'montagna' => 4]],
                    ['text' => 'Aperitivo in spiaggia', 'scores' => ['mare' => 5, 'divertimento' => 3]],
                    ['text' => 'Esplorare strade affollate', 'scores' => ['città' => 5, 'divertimento' => 2]]
                ]
            ],
            [
                'id' => 6,
                'question' => 'Chi è il tuo compagno di viaggio?',
                'answers' => [
                    ['text' => 'Solo', 'scores' => ['natura' => 1, 'città' => 1]],
                    ['text' => 'Coppia', 'scores' => ['mare' => 1, 'cultura' => 1]],
                    ['text' => 'Famiglia', 'scores' => ['natura' => 1, 'mare' => 1]],
                    ['text' => 'Gruppo di amici', 'scores' => ['divertimento' => 3, 'mare' => 1]]
                ]
            ],
            [
                'id' => 7,
                'question' => 'In che stagione vorresti partire?',
                'answers' => [
                    ['text' => 'Estate', 'scores' => ['mare' => 4, 'montagna' => 2]],
                    ['text' => 'Autunno', 'scores' => ['cultura' => 3, 'cibo' => 3]],
                    ['text' => 'Inverno', 'scores' => ['montagna' => 4, 'città' => 2]],
                    ['text' => 'Primavera', 'scores' => ['cultura' => 4, 'natura' => 3]]
                ]
            ],
        ];
    }

    public function submitQuiz($data)
    {
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
            $categoryScores = [
                'mare' => 0,
                'montagna' => 0,
                'città' => 0,
                'cultura' => 0,
                'divertimento' => 0,
                'natura' => 0,
                'storia' => 0,
                'cibo' => 0,
                'shopping' => 0,
                'tradizione' => 0,
                'relax' => 0,
                'tropicale' => 0
            ];
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
            $stmt = $this->db->prepare("
                SELECT c.nome, c.descrizione, c.categoria_viaggio, c.fascia_budget_base, p.nome as nome_paese 
                FROM citta c
                JOIN paesi p ON c.id_paese = p.id
                WHERE c.id_paese = ?
            ");
            $stmt->bind_param("i", $paeseId);
            $stmt->execute();
            $result = $stmt->get_result();

            $cittaList = [];
            while ($row = $result->fetch_assoc()) {
                $matchScore = 0;
                $cat = $row['categoria_viaggio']; // es: cultura, mare, città
                if (isset($categoryScores[$cat])) {
                    $matchScore += $categoryScores[$cat];
                }

                // Bonus budget
                $budgetAns = $answers[2] ?? 1; // Domanda 3 (indice 2) è il budget
                $budgetMap = [0 => 500, 1 => 1000, 2 => 1500, 3 => 2500];
                $userBudget = $budgetMap[$budgetAns] ?? 1000;

                if ($row['fascia_budget_base'] <= $userBudget) {
                    $matchScore += 5; // Bonus significativo se rientra nel budget
                } else {
                    $matchScore -= 5; // Malus se fuori budget
                }

                $row['match_score'] = $matchScore;
                $cittaList[] = $row;
            }

            // Se non ci sono città nel DB, usiamo dei default
            if (empty($cittaList)) {
                $bestCitta = ['nome' => 'Capitale', 'descrizione' => 'Esplora il cuore del paese!', 'match_score' => 0];
            } else {
                // Ordina e scegli la migliore
                usort($cittaList, function ($a, $b) {
                    return $b['match_score'] <=> $a['match_score'];
                });
                $bestCitta = $cittaList[0];
            }

            $flag = $this->getWikipediaFlag($bestCitta['nome']);
            if (!$flag) {
                // Fallback alla bandiera del paese se la città non ha immagine
                $flag = $this->getWikipediaFlag($bestCitta['nome_paese']);
            }

            $response = [
                'name' => $bestCitta['nome'],
                'description' => $bestCitta['descrizione'],
                'flag' => $flag
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

    public function runMigration()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!$this->db) {
                throw new Exception("Connessione DB non disponibile.");
            }

            $messages = [];

            // 1. Inserimento Paesi
            $paesiQuery = "INSERT IGNORE INTO paesi (nome, codice_iso, categorie_suggerite, descrizione) VALUES 
                ('Thailandia', 'THA', '[\"mare\", \"natura\", \"cibo\", \"cultura\", \"tropicale\"]', 'Spiagge tropicali, giungla lussureggiante e templi dorati'),
                ('Maldive', 'MDV', '[\"mare\", \"relax\", \"natura\", \"tropicale\"]', 'Atolli paradisiaci, acque cristalline e barriere coralline')";

            $this->db->query($paesiQuery);
            $messages[] = "Paesi inseriti o già presenti.";

            // 2. Recupero ID
            $resThai = $this->db->query("SELECT id FROM paesi WHERE nome = 'Thailandia'");
            $idThai = $resThai->fetch_assoc()['id'] ?? null;

            $resMaldive = $this->db->query("SELECT id FROM paesi WHERE nome = 'Maldive'");
            $idMaldive = $resMaldive->fetch_assoc()['id'] ?? null;

            if ($idThai && $idMaldive) {
                // 3. Inserimento Città
                $cittaQuery = "INSERT IGNORE INTO citta (nome, id_paese, categoria_viaggio, fascia_budget_base, descrizione, popolarita) VALUES 
                    ('Bangkok', $idThai, 'cultura', 1200.00, 'Metropoli vibrante, templi antichi e street food', 9),
                    ('Phuket', $idThai, 'mare', 1400.00, 'Spiagge tropicali, mare cristallino e vita notturna', 10),
                    ('Malé', $idMaldive, 'mare', 2000.00, 'Capitale delle Maldive, atoli e barriere coralline', 8)";

                $this->db->query($cittaQuery);
                $messages[] = "Città inserite o già presenti.";
            }

            echo json_encode(['success' => true, 'messages' => $messages]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
