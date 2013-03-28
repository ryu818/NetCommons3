<?php
define("NC_DATASOURCE", 'Database/Mysql');
define("NC_PERSISTENT", false);
define("NC_HOST", 'localhost');
define("NC_LOGIN", 'root');
define("NC_PASSWORD", 'mysql');
define("NC_DATABASE", 'masukawa_nc3');
define("NC_PREFIX", 'nc3_');
define("NC_ENCODING", 'utf8');
Configure::write('Security.salt', '0a6d1a099ba52aa434b321ab989f2c4f432d7006');
Configure::write('Security.cipherSeed', '2012248655989668915');

define("NC_INSTALLED", true);