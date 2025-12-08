-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 12:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sebastinian_showcase`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `action`, `details`, `timestamp`) VALUES
(1, 2, 'add_admin', 'Admin test created', '2025-12-06 23:28:33'),
(2, 2, 'delete_admin', 'Deleted admin ID 5', '2025-12-06 23:29:49'),
(3, 2, 'add_admin', 'Admin Try created', '2025-12-07 00:59:29'),
(4, 2, 'delete_admin', 'Deleted admin ID 6', '2025-12-07 00:59:40'),
(5, 2, 'delete_project', 'Deleted project PokéDexDB — Pokémon Database', '2025-12-07 05:54:44'),
(6, 2, 'project_status_update', 'Project 11 set to rejected', '2025-12-07 06:07:42'),
(7, 2, 'project_status_update', 'Project 12 set to approved', '2025-12-07 06:07:56'),
(8, 2, 'delete_project', 'Deleted project Test2', '2025-12-07 06:08:31'),
(9, 2, 'delete_project', 'Deleted project Test', '2025-12-07 06:08:33'),
(10, 2, 'delete_project', 'Deleted project PokéDexDB — Pokémon Database', '2025-12-07 06:08:34'),
(11, 2, 'add_admin', 'Admin Test created', '2025-12-07 06:09:58'),
(12, 2, 'project_status_update', 'Project 13 set to approved', '2025-12-07 07:14:03'),
(13, 2, 'project_status_update', 'Project 15 set to approved', '2025-12-08 04:34:01'),
(14, 2, 'project_status_update', 'Project 16 set to approved', '2025-12-08 09:13:15');

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `approval_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('approved','rejected') NOT NULL,
  `remarks` text DEFAULT NULL,
  `date_approved` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `likes` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `date_commented` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
  `like_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `comment_likes`
--
DELIMITER $$
CREATE TRIGGER `tr_comment_likes_after_delete` AFTER DELETE ON `comment_likes` FOR EACH ROW BEGIN
  UPDATE comments SET likes = GREATEST(likes - 1, 0) WHERE comment_id = OLD.comment_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_comment_likes_after_insert` AFTER INSERT ON `comment_likes` FOR EACH ROW BEGIN
  UPDATE comments SET likes = likes + 1 WHERE comment_id = NEW.comment_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `downloads_log`
--

CREATE TABLE `downloads_log` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT '',
  `file_size` int(11) UNSIGNED DEFAULT 0,
  `sdg_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `views` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `downloads` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_likes`
--

CREATE TABLE `project_likes` (
  `like_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sdgs`
--

CREATE TABLE `sdgs` (
  `sdg_id` int(11) NOT NULL,
  `sdg_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sdgs`
--

INSERT INTO `sdgs` (`sdg_id`, `sdg_name`) VALUES
(3, 'SDG 11 – Sustainable Cities & Communities'),
(4, 'SDG 13 – Climate Action'),
(1, 'SDG 4 – Quality Education'),
(2, 'SDG 9 – Industry, Innovation, and Infrastructure');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `role`, `date_created`, `profile_image`) VALUES
(1, 'Kae', '$2y$10$7A/guBYK9radV2UX0io8guuXsg2r5MhUhX9d0Phcie1BFkpbDmorG', 'Ken Adrien Arceno', 'kaearceno@gmail.com', 'student', '2025-11-30 14:48:18', NULL),
(2, 'Ken', '$2y$10$ljkmHd4tKG5Hz6IDv5uMSu.uUjqXh79eua9QhAx/RIVPzAXU/gpHK', 'Ken', 's.ken.adrien.arceno@sscr.edu', 'admin', '2025-12-06 06:15:32', NULL),
(7, 'Test', '$2y$10$GNJMdPpHL4K4trcxOBkkeu/YRiUBjPDupoig/2/Hbgindw6SlGlye', 'Test', 'Test@gmail.com', 'admin', '2025-12-07 06:09:58', NULL),
(8, 'TestUser', '$2y$10$SPgoMD7DafyJBdC1Z1MtmeJqHKM7pmwuQTGVBjaBRrCQ4T5rINI1W', 'TestUser', 'TestUser@gmail.com', 'student', '2025-12-07 06:25:04', NULL),
(9, 'User1', '$2y$10$qKx8w087QNQvIDhRojqh8urcUnpfO4VaCKPm27grYLFG4MIPZxBy2', 'User1', 'User@gmail.com', 'student', '2025-12-07 06:30:16', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_approved_projects`
-- (See below for the actual view)
--
CREATE TABLE `view_approved_projects` (
`project_id` int(11)
,`user_id` int(11)
,`title` varchar(255)
,`description` text
,`image` varchar(255)
,`file` varchar(255)
,`file_type` varchar(50)
,`file_size` int(11) unsigned
,`sdg_id` int(11)
,`status` enum('pending','approved','rejected')
,`date_submitted` timestamp
,`views` int(11) unsigned
,`downloads` int(11) unsigned
,`owner_name` varchar(100)
,`sdg_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_project_stats`
-- (See below for the actual view)
--
CREATE TABLE `view_project_stats` (
`project_id` int(11)
,`title` varchar(255)
,`user_id` int(11)
,`views` int(11) unsigned
,`downloads` int(11) unsigned
,`like_count` bigint(21)
,`comment_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `view_approved_projects`
--
DROP TABLE IF EXISTS `view_approved_projects`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_approved_projects`  AS SELECT `p`.`project_id` AS `project_id`, `p`.`user_id` AS `user_id`, `p`.`title` AS `title`, `p`.`description` AS `description`, `p`.`image` AS `image`, `p`.`file` AS `file`, `p`.`file_type` AS `file_type`, `p`.`file_size` AS `file_size`, `p`.`sdg_id` AS `sdg_id`, `p`.`status` AS `status`, `p`.`date_submitted` AS `date_submitted`, `p`.`views` AS `views`, `p`.`downloads` AS `downloads`, `u`.`full_name` AS `owner_name`, `s`.`sdg_name` AS `sdg_name` FROM ((`projects` `p` left join `users` `u` on(`p`.`user_id` = `u`.`user_id`)) left join `sdgs` `s` on(`p`.`sdg_id` = `s`.`sdg_id`)) WHERE `p`.`status` = 'approved' ;

-- --------------------------------------------------------

--
-- Structure for view `view_project_stats`
--
DROP TABLE IF EXISTS `view_project_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_project_stats`  AS SELECT `p`.`project_id` AS `project_id`, `p`.`title` AS `title`, `p`.`user_id` AS `user_id`, `p`.`views` AS `views`, `p`.`downloads` AS `downloads`, coalesce(`pl`.`like_count`,0) AS `like_count`, coalesce(`c`.`comment_count`,0) AS `comment_count` FROM ((`projects` `p` left join (select `project_likes`.`project_id` AS `project_id`,count(0) AS `like_count` from `project_likes` group by `project_likes`.`project_id`) `pl` on(`pl`.`project_id` = `p`.`project_id`)) left join (select `comments`.`project_id` AS `project_id`,count(0) AS `comment_count` from `comments` group by `comments`.`project_id`) `c` on(`c`.`project_id` = `p`.`project_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_activity_timestamp` (`timestamp`),
  ADD KEY `idx_activity_user_ts` (`user_id`,`timestamp`);

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `uk_setting_key` (`setting_key`);

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_date_commented` (`date_commented`);

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`comment_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `downloads_log`
--
ALTER TABLE `downloads_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sdg_id` (`sdg_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sdg_id` (`sdg_id`),
  ADD KEY `idx_date_submitted` (`date_submitted`);

--
-- Indexes for table `project_likes`
--
ALTER TABLE `project_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sdgs`
--
ALTER TABLE `sdgs`
  ADD PRIMARY KEY (`sdg_id`),
  ADD UNIQUE KEY `sdg_name` (`sdg_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- AUTO_INCREMENT for table `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `downloads_log`
--
ALTER TABLE `downloads_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `project_likes`
--
ALTER TABLE `project_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `sdgs`
--
ALTER TABLE `sdgs`
  MODIFY `sdg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `downloads_log`
--
ALTER TABLE `downloads_log`
  ADD CONSTRAINT `downloads_log_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`sdg_id`) REFERENCES `sdgs` (`sdg_id`) ON DELETE SET NULL;

--
-- Constraints for table `project_likes`
--
ALTER TABLE `project_likes`
  ADD CONSTRAINT `project_likes_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
