-- Script per aggiungere tabelle paesi e città
-- Eseguire questo script dopo aver importato si_parte.sql

-- Tabella paesi/stati
CREATE TABLE IF NOT EXISTS `paesi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `codice_iso` varchar(3) DEFAULT NULL,
  `categorie_suggerite` JSON DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabella città
CREATE TABLE IF NOT EXISTS `citta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `id_paese` int(11) NOT NULL,
  `categoria_viaggio` varchar(50) DEFAULT NULL,
  `fascia_budget_base` decimal(10,2) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `popolarita` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_paese` (`id_paese`),
  CONSTRAINT `fk_citta_paese` FOREIGN KEY (`id_paese`) REFERENCES `paesi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Aggiungi campo paese alla tabella users
ALTER TABLE `users` 
ADD COLUMN `paese_selezionato` varchar(100) DEFAULT NULL AFTER `destinazione_assegnata`;

-- Popolamento paesi (stati principali) senza immagini
INSERT INTO `paesi` (`nome`, `codice_iso`, `categorie_suggerite`, `descrizione`) VALUES
('Italia', 'ITA', '["cultura", "mare", "montagna", "cibo"]', 'Patria del patrimonio artistico, del cibo eccellente e delle coste mozzafiatto'),
('Spagna', 'ESP', '["mare", "cultura", "festa", "cibo"]', 'Sole, spiagge, cultura vibrante e vita notturna'),
('Francia', 'FRA', '["cultura", "cibo", "città", "montagna"]', 'Eleganza, arte, gastronomia e paesaggi vari'),
('Stati Uniti', 'USA', '["città", "natura", "divertimento", "shopping"]', 'Metropoli iconiche, parchi nazionali e intrattenimento'),
('Regno Unito', 'GBR', '["cultura", "città", "storia", "pub"]', 'Storia, cultura, tradizione e città cosmopolite'),
('Germania', 'DEU', '["cultura", "storia", "città", "montagna"]', 'Storia, cultura, birra e paesaggi montani'),
('Grecia', 'GRC', '["mare", "cultura", "storia", "isole"]', 'Isole paradisiache, storia antica e gastronomia mediterranea'),
('Giappone', 'JPN', '["cultura", "città", "cibo", "tradizione"]', 'Tradizione, modernità, cibo eccellente e cultura unica'),
('Portogallo', 'PRT', '["mare", "cultura", "cibo", "vino"]', 'Costa atlantica, cultura affascinante e vino Porto'),
('Svizzera', 'CHE', '["montagna", "natura", "città", "luxury"]', 'Alpi, precisione, cioccolato e paesaggi mozzafiatto'),
('Austria', 'AUT', '["montagna", "cultura", "musica", "sci"]', 'Alpi, musica classica, cultura e sport invernali'),
('Croazia', 'HRV', '["mare", "storia", "natura", "isole"]', 'Costa dalmata, città storiche e isole paradisiache'),
('Turchia', 'TUR', '["cultura", "storia", "mare", "cibo"]', 'Ponte tra Europa e Asia, storia ricca e cucina deliziosa'),
('Norvegia', 'NOR', '["natura", "montagna", "fiordi", "nordic"]', 'Fiordi spettacolari, aurore boreali e natura selvaggia'),
('Olanda', 'NLD', '["città", "biciclette", "cultura", "tulipani"]', 'Città storiche, canali, tulipani e cultura liberale'),
('Belgio', 'BEL', '["cultura", "cibo", "città", "storia"]', 'Cioccolato, birre, architettura gotica e cultura'),
('Polonia', 'POL', '["cultura", "storia", "città", "cibo"]', 'Storia ricca, città affascinanti e cucina tradizionale'),
('Repubblica Ceca', 'CZE', '["cultura", "storia", "birra", "città"]', 'Praga magica, birre eccellenti e storia affascinante'),
('Danimarca', 'DNK', '["città", "design", "cultura", "nordic"]', 'Design, hygge, città moderne e cultura nordica'),
('Svezia', 'SWE', '["natura", "città", "design", "nordic"]', 'Natura selvaggia, design, città moderne e cultura nordica');

-- Popolamento città (con riferimento ai paesi)
-- ITALIA
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('Roma', 1, 'cultura', 800.00, 'Città Eterna, patrimonio UNESCO, arte e storia', 10),
('Firenze', 1, 'cultura', 700.00, 'Culla del Rinascimento, arte e architettura', 9),
('Venezia', 1, 'cultura', 900.00, 'Città sull\'acqua, canali e romanticismo', 10),
('Milano', 1, 'città', 850.00, 'Capitale della moda e del design', 8),
('Napoli', 1, 'cultura', 600.00, 'Cultura, pizza e storia antica', 7),
('Amalfi', 1, 'mare', 900.00, 'Costa amalfitana, mare e paesaggi mozzafiato', 9),
('Cortina d\'Ampezzo', 1, 'montagna', 1200.00, 'Dolomiti, sci e montagna', 8);

-- SPAGNA
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('Barcellona', 2, 'mare', 1000.00, 'Architettura modernista, spiagge e vita notturna', 10),
('Madrid', 2, 'città', 950.00, 'Capitale vibrante, arte e cultura', 9),
('Siviglia', 2, 'cultura', 750.00, 'Flamenco, architettura moresca e tradizione', 8),
('Valencia', 2, 'mare', 800.00, 'Città delle arti, spiagge e paella', 7),
('Ibiza', 2, 'mare', 1100.00, 'Isole Baleari, vita notturna e spiagge', 9);

-- FRANCIA
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('Parigi', 3, 'città', 900.00, 'Città della Luce, arte, moda e romanticismo', 10),
('Lione', 3, 'cibo', 750.00, 'Capitale gastronomica della Francia', 7),
('Nizza', 3, 'mare', 950.00, 'Costa azzurra, sole e eleganza', 8),
('Chamonix', 3, 'montagna', 1100.00, 'Alpi francesi, sci e montagna', 8);

-- STATI UNITI
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('New York', 4, 'città', 1500.00, 'La Grande Mela, grattacieli, Broadway e shopping', 10),
('Los Angeles', 4, 'città', 1400.00, 'Hollywood, spiagge e lifestyle californiano', 9),
('Chicago', 4, 'città', 1200.00, 'Architettura, blues e cultura urbana', 8),
('Las Vegas', 4, 'divertimento', 1300.00, 'Casinò, spettacoli e intrattenimento', 9),
('Miami', 4, 'mare', 1400.00, 'Spiagge, vita notturna e art deco', 8);

-- REGNO UNITO
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('Londra', 5, 'città', 1100.00, 'Capitale cosmopolita, storia e cultura', 10),
('Edimburgo', 5, 'cultura', 900.00, 'Storia scozzese, castelli e festival', 8),
('Manchester', 5, 'città', 850.00, 'Musica, cultura e innovazione', 7);

-- GIAPPONE
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('Tokyo', 8, 'città', 1800.00, 'Metropoli futuristico, tecnologia e tradizione', 10),
('Kyoto', 8, 'cultura', 1600.00, 'Templi antichi, giardini zen e geishe', 10),
('Osaka', 8, 'cibo', 1500.00, 'Cibo eccellente, castello e intrattenimento', 8);

-- SVIZZERA
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('Zurigo', 10, 'città', 1300.00, 'Banche, lago e qualità della vita', 8),
('Ginevra', 10, 'città', 1400.00, 'Organizzazioni internazionali e lago', 7),
('Zermatt', 10, 'montagna', 1500.00, 'Matterhorn, sci e montagna', 9);

-- GRECIA
INSERT INTO `citta` (`nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `popolarita`) VALUES
('Atene', 7, 'cultura', 700.00, 'Storia antica, acropoli e archeologia', 9),
('Santorini', 7, 'mare', 1000.00, 'Isole cicladi, tramonti e mare azzurro', 10),
('Mykonos', 7, 'mare', 1100.00, 'Vita notturna, spiagge e divertimento', 9);

