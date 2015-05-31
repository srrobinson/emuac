<?php
require_once('../lib/ldap.php');



print "Welcome to account man!";

$ldap_conn = getLdapConnection();
createUser($ldap_conn, "OU=IT,OU=corp,DC=server,DC=local", "stuff", "Sergio", "Tuff", array('CN=Domain Admins,CN=Users,DC=server,DC=local'));