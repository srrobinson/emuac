<?php

require_once('constants.php');

/**
 * Used to create a DN in active directory, can be anything, depends on the props passed in.
 * @param resource $ldap_conn The LDAP connection used to connect to the server
 * @param string $distinguishedName The DN we want to create
 * @param array $props The Array of properties to be applied (also sets what the object is, e.g. User, Group etc)
 * @return int Return 0 if everything went according to plan, otherwise return the error code.
 */
function createObject($ldap_conn, $distinguishedName, $props)
{
    ldap_add($ldap_conn, $distinguishedName, $props);
    //print_r($props);
    if (ldap_error($ldap_conn) == "Success") {
        return 0;
    } else {
        return ldap_errno($ldap_conn);
    }
}

/**
 * Simple function to verify credentials against the LDAP server
 * @param resource $ldapConnection LDAP connection resource
 * @param string $ldapUser The user account to bind
 * @param string $ldapPass The password for that user account
 * @return bool result of the bind attempt, success if password verified, failed if not.
 */
function bind_to_server($ldapConnection, $ldapUser, $ldapPass)
{
    $ldapBind = @ldap_bind($ldapConnection, $ldapUser, $ldapPass);

    return $ldapBind;

}

/**
 * If the server is alive let's return a resource so people can connect to LDAP for things like binding and doing other
 * fun work with LDAP.
 * @return bool|resource False is server is dead, ldap_connect resource otherwise.
 */
function getLdapConnection()
{
    if (isServerAvailable(APP_LDAP_SERVER, APP_LDAP_PORT)) {
        return ldap_connect(APP_LDAP_SERVER, APP_LDAP_PORT);
    } else {
        return false;
    }

}

/**
 * This function is supposed to basically make sure that there is a server listening on a port for LDAP since
 * by default PHP will take about 30 seconds to time out a failed connection and give an error, this keeps us
 * from having to wait for that.
 * @param string $host The host we want to check
 * @param string $port The port on the host LDAP should be listening on
 * @param int $timeout How long we wait for the socket to fail (default 2 seconds)
 * @return bool Did we get a connection? If yes return True, otherwise false.
 */
function isServerAvailable($host, $port, $timeout = 2)
{
    $errCode = 0;
    $errMsg = "No Connection";
    $op = fsockopen($host, $port, $errCode, $errMsg, $timeout);
    if (!$op) {
        #host is down.
        return FALSE;
    } else {
        #clean up the connection and return success
        fclose($op);
        return TRUE;
    }
}