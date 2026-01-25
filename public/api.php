<?php
// public/api.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers per CORS e JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestione pre-flight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // 1. CARICAMENTO AUTOLOAD
    // Se Railway ha installato composer, il file è qui:
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    } else {
        // Fallback manuale se l'autoload non esiste (emergenza)
        require_once __DIR__ . '/../src/Database/Connection.php';
        require_once __DIR__ . '/../src/Controllers/QuizController.php';
    }

    // 2. CONTROLLO CLASSE
    if (!class_exists('\SiParte\Quiz\Controllers\QuizController')) {
        throw new Exception("Autoload fallito: La classe QuizController non è stata trovata. Verifica i namespace.");
    }

    // 3. INIZIALIZZAZIONE
    $quizController = new \SiParte\Quiz\Controllers\QuizController();
    
    $route = $_GET['route'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // 4. ROUTING
    if ($method === 'GET') {
        if ($route === 'quiz/questions') {
            // Questo DEVE funzionare anche senza DB perché i dati sono hardcoded
            $quizController->getQuestions();
        } elseif ($route === 'quiz/destinations') {
            $quizController->getDestinations();
        } else {
            throw new Exception("Rotta GET non valida: " . htmlspecialchars($route));
        }
    } elseif ($method === 'POST') {
        // Legge il body JSON
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if ($route === 'quiz/select-paese') {
            $quizController->selectPaese($input);
        } elseif ($route === 'quiz/submit') {
            $quizController->submitQuiz($input);
        } else {
            throw new Exception("Rotta POST non valida: " . htmlspecialchars($route));
        }
    } else {
        throw new Exception("Metodo non supportato");
    }

} catch (Throwable $e) {
    // Catch-all per qualsiasi errore (Database, Codice, Sintassi)
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()), // Solo il nome del file per sicurezza
        'line' => $e->getLine()
    ]);
}