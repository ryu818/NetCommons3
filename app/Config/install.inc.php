<?php
define("NC_DRIVER", 'Database/Mysql');
define("NC_PERSISTENT", false);
define("NC_HOST", 'localhost');
define("NC_LOGIN", 'root');
define("NC_PASSWORD", 'mysql');
define("NC_DATABASE", 'goto_nc3');
define("NC_PREFIX", 'nc3_');
define("NC_ENCODING", 'utf8');
Configure::write('Security.salt', 'bc54ded580ba86cc9f24e4b99f73d4a029dbb1ed');
Configure::write('Security.cipherSeed', '787530606982950793');

define("NC_INSTALLED", false);