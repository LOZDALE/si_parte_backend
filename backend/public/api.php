<?php
declare(strict_types=1);

use SiParte\Quiz\Controllers\QuizController;

// Mostra errori (solo in sviluppo/Docker)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers JSON + CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight request per CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Composer Autoload
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        throw new Exception('Composer autoload non trovato');
    }
    require_once $autoloadPath;

    // Controller
    if (!class_exists(QuizController::class)) {
        throw new Exception('QuizController non trovato (namespace/autoload)');
    }
    $quizController = new QuizController();

    // Routing
    $method = $_SERVER['REQUEST_METHOD'];
    $route  = trim($_GET['route'] ?? '', '/');

    if ($method === 'GET') {
        if ($route === 'quiz/questions') {
            $quizController->getQuestions();
        } elseif ($route === 'quiz/destinations') {
            // futuro: implementazione destinazioni
            echo json_encode(['success'=>true,'message'=>'Endpoint destinazioni non ancora implementato']);
            exit;
        } else {
            throw new Exception("Rotta GET non valida: $route");
        }

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($route === 'quiz/select-paese') {
            $quizController->selectPaese($input);
        } elseif ($route === 'quiz/submit') {
            $quizController->submitQuiz($input);
        } else {
            throw new Exception("Rotta POST non valida: $route");
        }

    } else {
        throw new Exception('Metodo HTTP non supportato');
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'file'    => basename($e->getFile()),
        'line'    => $e->getLine(),
    ]);
}
