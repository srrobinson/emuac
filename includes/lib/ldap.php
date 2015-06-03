<?php
/*
 * Copyright (c) 2015 srrobinson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
*/

require_once('constants.php');


/**
 * This function utilizes createObject to create a user object in AD.
 * This function currently assumes the users email will be set to first.lastname@domain.com
 * @param resource $ldap_conn LDAP connection to pass down to createObject
 * @param string $baseOU The base OU you want to drop the user object in.
 * @param string $username The users sAMAccountName
 * @param string $firstName The users first name
 * @param string $lastName The users last name
 * @param array $groups An array containing a list of group DNs you want them to be a member of.
 * @return int Return 0 if everything went according to plan, otherwise return the error code.
 */
function createUser($ldap_conn, $baseOU, $username, $firstName, $lastName, $groups)
{


    $fullName = $firstName . " " . $lastName;
    $userDN = "CN=" . $fullName . "," . $baseOU;

    $properties = array();

    $properties['cn'] = $fullName;
    $properties['givenName'] = $firstName;
    $properties['sn'] = $lastName;
    $properties['sAMAccountName'] = $username;
    $properties['UserPrincipalName'] = $username . "@" . APP_ROOT_DOMAIN;
    $properties['displayName'] = $fullName;
    $properties['name'] = $fullName;
    $properties['objectclass'][0] = 'top';
    $properties['objectclass'][1] = 'person';
    $properties['objectclass'][2] = 'organizationalPerson';
    $properties['objectclass'][3] = 'user';
    $properties['mail'] = $firstName . "." . $lastName . "@" . APP_ROOT_DOMAIN;




    $createResult = createObject($ldap_conn, $userDN, $properties);

    $groups = getGroupDNsFromSAM($ldap_conn, APP_LDAP_ROOT, $groups);
    //now lets see if we need to add them to any groups
    if (is_array($groups) && !empty($groups)) {
        addUserToManyGroups($ldap_conn, $userDN, $groups);
    } else {
        addUserToGroup($ldap_conn, $userDN, $groups);
    }

    if ($createResult) {
        return true;
    }

    return false;

}

/**
 * Used to create a DN in active directory, can be anything, depends on the props passed in.
 * @param resource $ldap_conn The LDAP connection used to connect to the server
 * @param string $distinguishedName The DN we want to create
 * @param array $properties The Array of properties to be applied (also sets what the object is, e.g. User, Group etc)
 * @return int Return 0 if everything went according to plan, otherwise return the error code.
 */
function createObject($ldap_conn, $distinguishedName, $properties)
{
    bind_to_server($ldap_conn, APP_LDAP_USER, APP_LDAP_PASS);

    ldap_add($ldap_conn, $distinguishedName, $properties);
    //print_r($props);

    if (ldap_error($ldap_conn) == "Success") {
        return 0;
    } else {
        return ldap_errno($ldap_conn);
    }
}

/**
 * Used to modify properties of an AD DN.
 * @param resource $ldap_conn The LDAP connection resource to connect to AD
 * @param string $distinguishedName The DN of the object we want to modify
 * @param array $properties The array of properties we want to set/update
 * @return int|string Return 0 if everything went according to plan, otherwise return the error code.
 */
function updateObject($ldap_conn, $distinguishedName, $properties)
{
    bind_to_server($ldap_conn, APP_LDAP_USER, APP_LDAP_PASS);

    ldap_modify($ldap_conn, $distinguishedName, $properties);
    //print_r($props);

    if (ldap_error($ldap_conn) == "Success") {
        return 0;
    } else {
        return ldap_error($ldap_conn);
    }
}

/**
 * This function calls on addUserToGroup to add someone to a group in AD since you can't modify memberOf, you have to
 * add them as a "member" to a group object.
 * @param resource $ldap_conn The LDAP Connection resource to connect to AD
 * @param String $userDistinguishedName The users DN who we want to add to thr group
 * @param array $groupList The array containing the group DNs we want them to become a member of.
 */
function addUserToManyGroups($ldap_conn, $userDistinguishedName, $groupList)
{
    foreach ($groupList as $group) {
        addUserToGroup($ldap_conn, $userDistinguishedName, $group);
    }
}

/**
 * This function adds a user to a group in AD
 * @param resource $ldap_conn The LDAP Connection resource to connect to AD
 * @param string $userDistinguishedName the DN of the user we want to add to the group.
 * @param string $groupDistinguishedName The DN of the group we want to add the user to.
 * @return bool Return true if we succeeded and false if we didn't
 */
function addUserToGroup($ldap_conn, $userDistinguishedName, $groupDistinguishedName)
{
    bind_to_server($ldap_conn, APP_LDAP_USER, APP_LDAP_PASS);
    $group_members['member'] = $userDistinguishedName;
    return ldap_mod_add($ldap_conn, $groupDistinguishedName, $group_members);


}

/**
 * This function takes in an array of sAMAccountNames for groups and converts it to an array of their DNs.
 * If any of them can't be matched, we just ignore it.
 * @param resource $ldap_conn The LDAP Connection resource to connect to AD.
 * @param string $baseOU The string representing the base for the search path.
 * @param array $groupList The array containing the sAMAccountNames for the groups.
 * @return array $groupDNList An array containing Strings representing the DNs for each sAMAccountName
 */
function getGroupDNsFromSAM($ldap_conn, $baseOU, $groupList)
{

    //needed in case user searches from the root of the domain
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    $groupDNList = array();
    //grab only the DN
    $attributes = array("dn");

    if (bind_to_server($ldap_conn, APP_LDAP_USER, APP_LDAP_PASS)) {
        foreach ($groupList as $group) {
            $filter = "(&(objectCategory=Group)(sAMAccountName=" . $group . "))";
            $result = ldap_search($ldap_conn, $baseOU, $filter, $attributes);
            $entries = ldap_get_entries($ldap_conn, $result);

            if ($entries['count'] > 0) {
                array_push($groupDNList, $entries[0]['dn']);
            }
        }
    }

    return $groupDNList;

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