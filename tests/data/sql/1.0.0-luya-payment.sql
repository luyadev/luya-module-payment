SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `payment_process` (
  `id` int(11) NOT NULL,
  `salt` varchar(120) NOT NULL,
  `hash` varchar(120) NOT NULL,
  `random_key` varchar(32) NOT NULL,
  `amount` int(11) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `provider_name` varchar(50) NOT NULL,
  `success_link` varchar(255) NOT NULL,
  `error_link` varchar(255) NOT NULL,
  `abort_link` varchar(255) NOT NULL,
  `transaction_config` text NOT NULL,
  `close_state` int(11) DEFAULT '0',
  `is_closed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payment_process_trace` (
  `process_id` int(11) NOT NULL,
  `event` varchar(250) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  `get` text,
  `post` text,
  `server` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `payment_process`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash` (`hash`),
  ADD UNIQUE KEY `random_key` (`random_key`);

ALTER TABLE `payment_process`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;