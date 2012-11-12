<?php
define("NC_DATASOURCE", 'Database/Mysql');
define("NC_PERSISTENT", false);
define("NC_HOST", 'localhost');
define("NC_LOGIN", '');
define("NC_PASSWORD", '');
define("NC_DATABASE", '');
define("NC_PREFIX", 'nc3_');
define("NC_ENCODING", 'utf8');
Configure::write('Security.salt', '');
Configure::write('Security.cipherSeed', '');

define("NC_INSTALLED", false);