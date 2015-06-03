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
require_once('../includes/lib/ldap.php');
require_once('../includes/lib/functions.php');

include('../includes/html/header.php');


//if we don't at least have these, we need to print the form

if (does_post_var_exist("first_name") && does_post_var_exist("last_name")) {


    //ternary magic to make sure all input is present so we can check it later in one statement.
    $firstNames = is_field_blank("first_name") ? null : get_post_var("first_name");
    $lastNames = is_field_blank("last_name") ? null : get_post_var("last_name");
    $usernames = is_field_blank("username") ? null : get_post_var("username");

    $baseOU = is_field_blank("base_ou") ? null : get_post_var("base_ou");

    $groupList = is_field_blank("group_list") ? null : get_post_var("group_list");

    $sendEmail = false;
    $emailText = null;
    if (does_post_var_exist("enable_email")) {
        $sendEmail = true;
        $emailText = is_field_blank("email_text") ? null : get_post_var("email_text");
    }

    //if any of these fields are null we need to give an error
    if ($firstNames == null || $lastNames == null || $usernames == null || $baseOU == null | $groupList == null ||
        ($sendEmail && $emailText == null)
    ) {

        print_error("Please enter all required field data and try again.");


    } else {
        //sweet call back method to both trim whitepace around each element and dump their trimmed version into array
        $groupList = array_map('trim', explode(',', $groupList));
        $ldap_connection = getLdapConnection();
        foreach ($firstNames as $firstNameKey => $firstNameValue) {
            createUser($ldap_connection, $baseOU, $usernames[$firstNameKey], $firstNameValue, $lastNames[$firstNameKey], $groupList);
        }

        print "We made it!";

    }



} else {
    include('../includes/html/input-form.php');
}


include('../includes/html/footer.php');