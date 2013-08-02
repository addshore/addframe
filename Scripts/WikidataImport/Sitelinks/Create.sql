/* This script will create the table expected by the script */
/* @todo fix the name of the table? */

CREATE TABLE `iwlink` (
`site` char(10) NOT NULL,
`lang` char(12) NOT NULL,
`namespace` smallint(6) NOT NULL,
`title` char(200) NOT NULL,
`links` smallint(6) DEFAULT NULL,
`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`log` char(250) DEFAULT NULL,
PRIMARY KEY (`site`,`lang`,`namespace`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8