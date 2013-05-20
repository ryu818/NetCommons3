<?php
define("NC_DATASOURCE", 'Database/Mysql');
define("NC_PERSISTENT", false);
define("NC_HOST", 'localhost');
define("NC_LOGIN", 'root');
define("NC_PASSWORD", 'mysql');
define("NC_DATABASE", 'masukawa_nc3');
define("NC_PREFIX", 'nc3_');
define("NC_ENCODING", 'utf8');
Configure::write('Security.salt', '2c513d985439ec28e0dc1f95e352b99d5fb5622c');
Configure::write('Security.cipherSeed', '2135448167212875942');

define("NC_INSTALLED", true);