DROP TABLE IF EXISTS `oc_webposbank`;
CREATE TABLE IF NOT EXISTS `oc_webposbank` (
`bank_id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(64) NOT NULL,
`image` varchar(64) NOT NULL,
`method` varchar(64) NOT NULL,
`model` varchar(64) NOT NULL,
`short` varchar(64) NOT NULL,
`status` tinyint(1) NOT NULL,
PRIMARY KEY (`bank_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;