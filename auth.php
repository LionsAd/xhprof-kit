<?php

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'xhprof-kit');

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] != ADMIN_USERNAME || $_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD) {
	Header("WWW-Authenticate: Basic realm=\"XHProf-Kit Login\"");
	Header("HTTP/1.0 401 Unauthorized");
	echo <<<EOF
<html><body>
<h1>Rejected!</h1>
<big>Wrong Username or Password!</big><br/>&nbsp;<br/>&nbsp;
</body></html>
EOF;
	exit(1);
}
