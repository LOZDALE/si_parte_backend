<?php
// public/migrate.php
require_once __DIR__ . '/../vendor/autoload.php';
use SiParte\Quiz\Database\Connection;

header('Content-Type: text/plain');

try {
    $db = Connection::getInstance();
    echo "Connessione stabilita con successo.\n";

    // 1. Inserimento Paesi
    $paesiQuery = "INSERT IGNORE INTO paesi (nome, codice_iso, categorie_suggerite, descrizione) VALUES 
        ('Thailandia', 'THA', '[\"mare\", \"natura\", \"cibo\", \"cultura\", \"tropicale\"]', 'Spiagge tropicali, giungla lussureggiante e templi dorati'),
        ('Maldive', 'MDV', '[\"mare\", \"relax\", \"natura\", \"tropicale\"]', 'Atolli paradisiaci, acque cristalline e barriere coralline')";

    if ($db->query($paesiQuery)) {
        echo "Paesi inseriti o già presenti.\n";
    }

    // 2. Recupero ID
    $resThai = $db->query("SELECT id FROM paesi WHERE nome = 'Thailandia'");
    $idThai = $resThai->fetch_assoc()['id'] ?? null;

    $resMaldive = $db->query("SELECT id FROM paesi WHERE nome = 'Maldive'");
    $idMaldive = $resMaldive->fetch_assoc()['id'] ?? null;

    if ($idThai && $idMaldive) {
        // 3. Inserimento Città
        $cittaQuery = "INSERT IGNORE INTO citta (nome, id_paese, categoria_viaggio, fascia_budget_base, descrizione, popolarita) VALUES 
            ('Bangkok', $idThai, 'cultura', 1200.00, 'Metropoli vibrante, templi antichi e street food', 9),
            ('Phuket', $idThai, 'mare', 1400.00, 'Spiagge tropicali, mare cristallino e vita notturna', 10),
            ('Malé', $idMaldive, 'mare', 2000.00, 'Capitale delle Maldive, atoli e barriere coralline', 8)";

        if ($db->query($cittaQuery)) {
            echo "Città inserite o già presenti.\n";
        }
    } else {
        echo "Errore: ID paesi non trovati.\n";
    }

    echo "\nMigrazione completata con successo!";

} catch (Exception $e) {
    echo "ERRORE durante la migrazione: " . $e->getMessage();
}
