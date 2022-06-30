-- Généré le : sam. 18 juin 2022 à 17:08
-- Version du serveur :  5.7.31
-- Version de PHP : 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ocp5_blog`
--
CREATE DATABASE IF NOT EXISTS `ocp5_blog` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ocp5_blog`;

-- --------------------------------------------------------

--
-- Structure de la table `blog_comment`
--

DROP TABLE IF EXISTS `blog_comment`;
CREATE TABLE IF NOT EXISTS `blog_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` datetime DEFAULT NULL,
  `comment` text NOT NULL,
  `is_validated` tinyint(1) DEFAULT NULL,
  `validation_comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comment_author` (`author_id`),
  KEY `comment_blog` (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `blog_post`
--

DROP TABLE IF EXISTS `blog_post`;
CREATE TABLE IF NOT EXISTS `blog_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` datetime DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `caption` text NOT NULL,
  `content` mediumtext NOT NULL,
  `deleted_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_author` (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `blog_post`
--

INSERT INTO `blog_post` (`id`, `author_id`, `created_on`, `modified_on`, `title`, `caption`, `content`, `deleted_on`) VALUES
(1, 1, '2021-11-19 21:14:00', NULL, 'First blog-post', 'It\'s your first blog post !\r\n', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Sed cras ornare arcu dui vivamus. Lacus laoreet non curabitur gravida. Risus ultricies tristique nulla aliquet enim tortor at. Auctor augue mauris augue neque. Enim nulla aliquet porttitor lacus luctus accumsan tortor posuere ac. Lacus sed viverra tellus in hac habitasse. Id ornare arcu odio ut sem nulla pharetra diam. Senectus et netus et malesuada fames ac turpis egestas maecenas. Id interdum velit laoreet id donec ultrices. Lorem sed risus ultricies tristique nulla. Nisl pretium fusce id velit ut tortor pretium. Quis eleifend quam adipiscing vitae proin sagittis nisl rhoncus. Eget egestas purus viverra accumsan in. Nunc faucibus a pellentesque sit amet porttitor eget dolor morbi. Mi quis hendrerit dolor magna eget est lorem ipsum. Sed velit dignissim sodales ut eu. Elementum sagittis vitae et leo duis ut. Et egestas quis ipsum suspendisse ultrices gravida dictum fusce. Tellus mauris a diam maecenas sed enim ut sem viverra.\r\n\r\nElementum nisi quis eleifend quam adipiscing vitae proin sagittis nisl. Dui accumsan sit amet nulla facilisi morbi tempus iaculis urna. Nulla facilisi morbi tempus iaculis urna id volutpat lacus laoreet. Tortor vitae purus faucibus ornare suspendisse sed nisi lacus. Lectus nulla at volutpat diam ut venenatis tellus in metus. Cursus eget nunc scelerisque viverra mauris in aliquam. Ipsum faucibus vitae aliquet nec. Purus sit amet luctus venenatis lectus magna fringilla urna. Non consectetur a erat nam at lectus. Blandit massa enim nec dui nunc mattis. Magna fringilla urna porttitor rhoncus dolor purus.\r\n\r\nSuspendisse potenti nullam ac tortor. Morbi enim nunc faucibus a pellentesque sit. Pharetra convallis posuere morbi leo urna. A diam sollicitudin tempor id eu. Urna nunc id cursus metus aliquam eleifend mi. Dui sapien eget mi proin sed libero. Nulla aliquet enim tortor at auctor urna nunc id cursus. Dapibus ultrices in iaculis nunc sed augue lacus viverra. Convallis tellus id interdum velit laoreet id. Sapien nec sagittis aliquam malesuada bibendum arcu. Vitae semper quis lectus nulla at volutpat. Vulputate eu scelerisque felis imperdiet proin. Viverra orci sagittis eu volutpat odio facilisis. Justo nec ultrices dui sapien eget. Ultrices tincidunt arcu non sodales neque sodales ut etiam sit. Vel orci porta non pulvinar.', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `role` tinyint(4) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `session_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `pwd`, `created_on`, `deleted_on`, `role`, `is_active`, `session_token`) VALUES
(1, 'admin', 'your.mail@demos.com', '$2y$10$4z6/7dfNRZwmd5MLLId1bOCRUqY6z6l0NwVDzzC7Efjiicr4iUAe.', '2021-11-19 21:11:41', NULL, 1, 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `user_session`
--

DROP TABLE IF EXISTS `user_session`;
CREATE TABLE IF NOT EXISTS `user_session` (
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(39) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`,`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `blog_comment`
--
ALTER TABLE `blog_comment`
  ADD CONSTRAINT `comment_author` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comment_blog` FOREIGN KEY (`post_id`) REFERENCES `blog_post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `blog_post`
--
ALTER TABLE `blog_post`
  ADD CONSTRAINT `blog_author` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_session`
--
ALTER TABLE `user_session`
  ADD CONSTRAINT `user_session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
