-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 23 mai 2026 à 09:23
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `novagroup`
--

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `country` varchar(10) NOT NULL,
  `iban` varchar(60) NOT NULL,
  `bank_name` varchar(120) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'EUR',
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `dob`, `email`, `password`, `phone`, `country`, `iban`, `bank_name`, `currency`, `balance`, `is_admin`, `created_at`) VALUES
(2, 'Admin', 'Root', '1990-01-01', 'admin@gmail.com', '$2y$10$PmmSwF9saK8qgVqi4q3v/u5TBR3SdlDZyzN/Y4wEIerU723L1nbIC', '00000000', 'BJ', 'ADMINIBAN123', 'Nova Bank', 'XOF', 0.00, 1, '2026-04-29 09:03:01'),
(3, 'Omar', 'Ahmad', '2006-01-31', 'omar@gmail.com', '$2y$10$SJXM4uwrICrVCC4f6z/FWeQQwDuuCqW13jQlEzC8GDLs.Hiy33Bci', '21325489', 'PL', 'FR 31321321321321', 'BOA', 'PLN', 500000.00, 0, '2026-04-29 09:08:01'),
(4, 'Thierry', 'Henry', '2005-12-10', 'henry@gmail.com', '$2y$10$jSn/Q8PAlYvW72.Gp7.cYeHzIvLrmnL3lOLzSr8S9Fax.tablFvWO', '21325489', 'PT', 'PT 6546546865465', 'PTB', 'EUR', 100.00, 0, '2026-04-29 09:21:33'),
(6, 'Thomas', 'BOKO', '1999-05-02', 'thomas@gmail.com', '$2y$10$91HyPXFXHhjmtun5fPPa/.SeFtcYf6mAPWeJf6ZfSL6wuUDsyS076', '65489654651', 'CA', 'FR 6565454665465', 'BOA', 'CAD', 500.00, 0, '2026-04-29 15:59:24');

-- --------------------------------------------------------

--
-- Structure de la table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `step` tinyint(4) NOT NULL DEFAULT 0,
  `current_pct` tinyint(4) NOT NULL DEFAULT 0,
  `target_pct` tinyint(4) NOT NULL DEFAULT 20,
  `code_requested` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `ben_firstname` varchar(100) DEFAULT NULL,
  `ben_lastname` varchar(100) DEFAULT NULL,
  `ben_bank` varchar(120) DEFAULT NULL,
  `ben_iban` varchar(60) DEFAULT NULL,
  `ben_bic` varchar(20) DEFAULT NULL,
  `ben_phone` varchar(30) DEFAULT NULL,
  `ben_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `user_id`, `amount`, `currency`, `step`, `current_pct`, `target_pct`, `code_requested`, `status`, `ben_firstname`, `ben_lastname`, `ben_bank`, `ben_iban`, `ben_bic`, `ben_phone`, `ben_reason`, `created_at`, `updated_at`) VALUES
(1, 3, 100.00, 'PLN', 5, 100, 100, 0, 'failed', 'Omar', 'Ahmad', 'BOA', 'FR 31321321321321', 'BOA', '21325489', 'Cadeau', '2026-04-29 09:11:48', '2026-04-29 09:16:36'),
(2, 4, 100.00, 'EUR', 5, 100, 100, 0, 'failed', 'Thierry', 'Henry', 'PTB', 'PT 6546546865465', 'BOA', '21325489', 'Cadeau', '2026-04-29 12:33:59', '2026-04-29 12:39:05'),
(3, 6, 100.00, 'CAD', 3, 60, 80, 1, 'pending', 'Thomas', 'BOKO', 'BOA', 'FR 6565454665465', 'BOA', '65489654651', 'Cadeau', '2026-04-29 16:01:47', '2026-04-29 16:04:53');

-- --------------------------------------------------------

--
-- Structure de la table `withdrawal_codes`
--

CREATE TABLE `withdrawal_codes` (
  `id` int(11) NOT NULL,
  `withdrawal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` char(6) NOT NULL,
  `step` tinyint(4) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `withdrawal_codes`
--

INSERT INTO `withdrawal_codes` (`id`, `withdrawal_id`, `user_id`, `code`, `step`, `used`, `created_at`) VALUES
(1, 1, 3, '439132', 1, 1, '2026-04-29 09:12:00'),
(2, 1, 3, '911574', 2, 1, '2026-04-29 09:12:31'),
(3, 1, 3, '417001', 3, 1, '2026-04-29 09:13:25'),
(4, 1, 3, '595574', 4, 1, '2026-04-29 09:13:52'),
(5, 1, 3, '337392', 5, 1, '2026-04-29 09:15:47'),
(6, 2, 4, '995340', 1, 1, '2026-04-29 12:34:11'),
(7, 2, 4, '783706', 2, 1, '2026-04-29 12:35:17'),
(8, 2, 4, '398084', 3, 1, '2026-04-29 12:35:44'),
(9, 2, 4, '679904', 4, 1, '2026-04-29 12:38:19'),
(10, 2, 4, '291800', 5, 1, '2026-04-29 12:38:49'),
(11, 3, 6, '547784', 1, 1, '2026-04-29 16:01:59'),
(12, 3, 6, '390008', 2, 1, '2026-04-29 16:03:34'),
(13, 3, 6, '180500', 3, 1, '2026-04-29 16:04:00'),
(14, 3, 6, '886717', 4, 0, '2026-04-29 16:04:53');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `withdrawal_codes`
--
ALTER TABLE `withdrawal_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdrawal_id` (`withdrawal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `withdrawal_codes`
--
ALTER TABLE `withdrawal_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `withdrawal_codes`
--
ALTER TABLE `withdrawal_codes`
  ADD CONSTRAINT `withdrawal_codes_ibfk_1` FOREIGN KEY (`withdrawal_id`) REFERENCES `withdrawals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `withdrawal_codes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
