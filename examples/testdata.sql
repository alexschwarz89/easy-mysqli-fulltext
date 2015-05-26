SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `testdata` (
`id` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `testdata`
--

INSERT INTO `testdata` (`id`, `description`) VALUES
(1, 'Just an example. Nothing special.'),
(2, 'This is another example, but again, nothing special.'),
(3, 'This is something completely different and not containing the word mentioned in the other testdata entries.'),
(4, 'Another demodata.'),
(5, 'Five is enough.');

--
-- Indexes for table `testdata`
--
ALTER TABLE `testdata`
 ADD PRIMARY KEY (`id`), ADD FULLTEXT KEY `description` (`description`);

--
-- AUTO_INCREMENT for table `testdata`
--
ALTER TABLE `testdata`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
