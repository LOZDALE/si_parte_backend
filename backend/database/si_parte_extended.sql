-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Dic 07, 2025
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `si_parte`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `nome_destinazione` varchar(100) DEFAULT NULL,
  `categoria_viaggio` varchar(50) DEFAULT NULL,
  `fascia_budget_base` decimal(10,2) DEFAULT NULL,
  `scenari_simulazione_fase2` JSON DEFAULT NULL,
  `attività_extra_fase3` JSON DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `destinations`
--

INSERT INTO `destinations` (`id`, `nome_destinazione`, `categoria_viaggio`, `fascia_budget_base`, `scenari_simulazione_fase2`, `attività_extra_fase3`) VALUES
(1, 'Roma', 'cultura', 800.00, '{}', '{}'),
(2, 'Barcellona', 'mare', 1000.00, '{}', '{}'),
(3, 'Alpi', 'montagna', 1200.00, '{}', '{}'),
(4, 'Parigi', 'città', 900.00, '{}', '{}');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `risposte_quiz` JSON NOT NULL,
  `destinazione_assegnata` varchar(100) DEFAULT NULL,
  `tipo_viaggio` varchar(100) DEFAULT NULL,
  `budget_finale` decimal(10,2) DEFAULT NULL,
  `scelte_extra` JSON DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `risposte_quiz`, `destinazione_assegnata`, `tipo_viaggio`, `budget_finale`, `scelte_extra`, `email`) VALUES
(1, '{"climate":"mare","activities":["relax","cultura"],"budget":1000}', 'Barcellona', 'Relax & Cultura', 1000.00, '{}', 'demo@example.com');

-- --------------------------------------------------------
--
-- Indici per le tabelle scaricate
--

-- Indici per la tabella `destinations`
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

-- Indici per la tabella `users`
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------
--
-- AUTO_INCREMENT per le tabelle scaricate
--

-- AUTO_INCREMENT per la tabella `destinations`
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- AUTO_INCREMENT per la tabella `users`
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
