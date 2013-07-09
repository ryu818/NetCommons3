<?php
define("NC_DATASOURCE", 'Database/Mysql');
define("NC_PERSISTENT", false);
define("NC_HOST", 'localhost');
define("NC_LOGIN", 'root');
define("NC_PASSWORD", 'mysql');
define("NC_DATABASE", 'masukawa_nc3');
define("NC_PREFIX", 'nc3_');
define("NC_ENCODING", 'utf8');
Configure::write('Security.salt', '02c8ba1903f665fa999997c466d1608d8e1bc896');
Configure::write('Security.cipherSeed', '520090157372772249');

define("NC_INSTALLED", true);