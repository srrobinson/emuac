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
    $rand_pass = is_field_blank("rand_pass") ? null : get_post_var("rand_pass");
    $set_pass = is_field_blank("set_pass") ? null : get_post_var("set_pass");


    $baseOU = is_field_blank("base_ou") ? null : get_post_var("base_ou");

    $groupList = is_field_blank("group_list") ? null : get_post_var("group_list");

    $sendEmail = false;
    $emailText = null;
    if (does_post_var_exist("enable_email")) {
        $sendEmail = true;
        $emailText = is_field_blank("email_text") ? null : $_POST['email_text'];
    }

    //if any of these fields are null we need to give an error
    if ($firstNames == null || $lastNames == null || $usernames == null || $baseOU == null | $groupList == null ||
        ($sendEmail && $emailText == null) || (!$rand_pass && !$set_pass)
    ) {

        print_error("Please enter all required field data and try again.");


    } else {

        $emailDomain = APP_ROOT_DOMAIN;

        //if a custom domain wasn't sent, assume the AD domain is the email domain
        if (SMTP_DOMAIN != null) {
            $emailDomain = SMTP_DOMAIN;
        }
        //sweet call back method to both trim whitepace around each element and dump their trimmed version into array
        $groupList = array_map('trim', explode(',', $groupList));

        $ldap_connection = getLdapConnection();
        foreach ($firstNames as $userKey => $firstNameValue) {
            $password = null;
            if ($rand_pass) {
                $password = generatePassword(RAND_PASSWORD_LENGTH, RAND_PASSWORD_SPECIAL_CHARS);
            } else {
                $password = $set_pass;
            }

            $userEmail = getEmail($firstNameValue, $lastNames[$userKey], $emailDomain);
            $userDN = createUser($ldap_connection, $baseOU, $usernames[$userKey], $userEmail, $firstNameValue, $lastNames[$userKey], $password, $groupList);

            if ($userDN) {
                print "<br />Created user: " . $usernames[$userKey] . " with password of: " . $password . "<br /><br />";

                if ($sendEmail) {

                    $templateData = array("{firstname}" => $firstNameValue, "{lastname}" => $lastNames[$userKey], "{username}" => $usernames[$userKey], "{password}" => $password);
                    $mailer = prepareEmail($userEmail, SMTP_FROM, SMTP_SUBJECT, $emailText, $templateData);

                    //don't overload the mail server
                    sleep(1);

                    if ($mailer->send()) {
                        print "Email sent correctly!";
                    } else {
                        print "Email failed to send, error was: " . $mailer->ErrorInfo;
                    }
                }
            } else {
                print "<br />User: " . $usernames[$userKey] . " was not created successfully <br />";
            }

        }
        print "<h3>Please record this password information, it won't be retrievable once you leave this page!</h3>";

        print "<a href=\"index.php\">Return Home</a>";

    }


} else {
    include('../includes/html/input-form.php');
}


include('../includes/html/footer.php');