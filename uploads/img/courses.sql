-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 15 jan. 2025 à 11:26
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `youdemy`
--

-- --------------------------------------------------------

--
-- Structure de la table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `media` varchar(255) DEFAULT NULL,
  `teacherId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `isApproved` tinyint(1) DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `approvedBy` int(11) DEFAULT NULL,
  `approvedAt` datetime DEFAULT NULL,
  `rejectedBy` int(11) DEFAULT NULL,
  `rejectedAt` datetime DEFAULT NULL,
  `rejectionReason` text DEFAULT NULL,
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `thumbnail`, `media`, `teacherId`, `categoryId`, `price`, `isApproved`, `createdAt`, `deleted_at`, `deleted_by`, `approvedBy`, `approvedAt`, `rejectedBy`, `rejectedAt`, `rejectionReason`, `updatedAt`) VALUES
(3, 'Learn django in 15 Days (Crash Course)', 'The best crash course', 'uploads/thumbnails/6786457a04567_1337368.png', 'uploads/content/6786457a048ac_UseCaseBlog_LV (1).pdf', 4, 1, 200.00, -1, '2025-01-14 11:07:38', NULL, NULL, NULL, NULL, 3, '2025-01-14 12:18:45', 'rzzg', '2025-01-14 14:36:31'),
(4, 'simantiiig', 'simaaaaaaaaaaaaaaaantiiiiiiiiiiiiiiiiiiig', 'uploads/thumbnails/67866a2a3bb2c_petits-pains-maison-sucres-fraichement-cuits-cuisson-partir-seigle-farine-vue-dessus-style-rustique_187166-7741.jpg', 'uploads/content/67866a2a3cc02_CNDP-loi-09-08-Liste-des-infractions-sanctions-fr.pdf', 10, 1, 500.00, 1, '2025-01-14 13:44:10', NULL, NULL, 3, '2025-01-14 16:23:44', NULL, NULL, NULL, '2025-01-14 15:23:44'),
(5, 'dfgh', 'gjetyj', 'uploads/thumbnails/67866bd9af41a_pexels-pashal-337909.jpg', 'uploads/content/67866bd9afe59_WhatsApp Video 2025-01-05 at 16.34.00.mp4', 7, 4, 45.00, -1, '2025-01-14 13:51:21', NULL, NULL, NULL, NULL, 3, '2025-01-14 16:24:02', 'low content', '2025-01-14 15:24:02'),
(6, 'Natus esse ab delect', 'Lorem quis eum conse', 'uploads/thumbnails/6786741bbff1d_pexels-pashal-337909.jpg', 'uploads/content/6786741bc0953_UseCaseBlog_LV (1).pdf', 7, 1, 767.00, 0, '2025-01-14 14:26:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-14 14:36:31');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacherId` (`teacherId`),
  ADD KEY `categoryId` (`categoryId`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `approvedBy` (`approvedBy`),
  ADD KEY `rejectedBy` (`rejectedBy`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacherId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`approvedBy`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `courses_ibfk_4` FOREIGN KEY (`rejectedBy`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
