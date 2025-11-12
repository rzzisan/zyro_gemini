CREATE TABLE `courier_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `courier_name` varchar(255) NOT NULL DEFAULT 'steadfast',
  `phone_number` varchar(20) NOT NULL,
  `total_orders` int(11) NOT NULL DEFAULT 0,
  `total_delivered` int(11) NOT NULL DEFAULT 0,
  `total_cancelled` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone_number` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
