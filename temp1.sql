USE temperature;

CREATE TABLE IF NOT EXISTS `alarm` (
  `alarmno` int(11) NOT NULL AUTO_INCREMENT,
  `sensor` tinyint(4) NOT NULL,
  `date` datetime NOT NULL,
  `valuemaxalarm` float NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`alarmno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `temp` (
  `sensor` tinyint(4) NOT NULL,
  `value` float NOT NULL,
  `created` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
