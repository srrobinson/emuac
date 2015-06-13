<?php
require_once('defuse/PasswordGenerator.php');
require_once('phpmailer/PHPMailerAutoload.php');
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

/***
 * Function to check if a variable was ever POSTed to the server
 * @param mixed $varname the given variable to check
 * @return bool return whether the variable exists in the post array
 */
function does_post_var_exist($varname)
{
    return isset($_POST[$varname]);
}

/***
 * Return a sanitized version of a post variable.
 * @param mixed $varname name of the post variable.
 * @param bool $noWhiteSpace Boolean whether we want to strip out all white space or not.
 * (might not want to do that if its coming from textarea)
 * @return string the value of that post variable.
 */

function get_post_var($varname, $noWhiteSpace = true)
{
    return sanitize_data($_POST[$varname], $noWhiteSpace);
}

/***
 * Perform some basic input sanitization (whitespace, html and php), not DB safe.
 * @param mixed $inputData data to be sanitized
 * @param bool $noWhiteSpace Set to true if you want all newlines/extra white space removed
 * @return string return the sanitized string input or sanitized array
 */
function sanitize_data($inputData, $noWhiteSpace = true)
{
    if (is_array($inputData)) {
        foreach ($inputData as $key => $data) {
            if ($noWhiteSpace) {
                $inputData[$key] = trim(stripslashes(strip_tags($data)));
            } else {
                $inputData[$key] = stripslashes(strip_tags($data));
            }
        }
        return $inputData;
    } else {
        if ($noWhiteSpace) {

            return trim(stripslashes(strip_tags($inputData)));
        } else {
            return stripslashes(strip_tags($inputData));
        }
    }
}

/***
 * Wrapper function around is_numeric, can run against arrays of values or single value.
 * @param mixed $varname variable we are checking.
 * @return bool return true if its numeric, false if not
 */
function is_var_numeric($varname)
{
    $inputData = $_POST[$varname];
    if (is_array($inputData)) {
        foreach ($inputData as $key => $data) {
            if (!is_numeric($data)) {
                return false;
            }
        }
    } else {
        return is_numeric($inputData);
    }
    return true;
}

/**
 * Verify if the variable is blank or not. Can check arrays of values for blanks.
 * @param mixed $varname POST variable name to check
 * @return bool true is any of the values in an array are blank or single variable, false if nothing is blank
 */
function is_field_blank($varname)
{
    //catch things like unchecked checkboxes
    if (!isset($_POST[$varname])) {
        return true;
    } else {
        $input_data = $_POST[$varname];
        if (is_array($input_data)) {
            foreach ($input_data as $key => $data) {
                if (empty($data)) {
                    return true;
                }
            }
        } else {
            return empty($input_data);
        }
    }
}


/**
 * Print out a standardized error message if someone tries ot enter bad data.
 * @param $message string the message to be displayed
 */
function print_error($message)
{
    print '<h3 class="error">' . $message . '</h3>';
    print '<a class="go_back_link" href="javascript:void(0);">Go Back</a>';

}

/**
 * Wrapper around filter_var for checking email
 * @param $email string the email to validate
 * @return bool return true is the email is valid or false if it isn't valid.
 */
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
}

/**
 * Simple wrapper around strtr to replace "template" values with actual values based on data in an associative
 * array (formValues).
 * @param string $originalText The original templated text
 * @param array $formValues The associative array containing the values to be replaced in originalText
 * @return string The updated text with the newly replaced values.
 */
function fill_in_template($originalText, $formValues)
{
    return strtr($originalText, $formValues);
}

/**
 * This function takes basic email information in and a originalBody and templateData.
 * The body template contains "template fields" such as {first_name} which is matched as a key within formValues
 * and replaced with the value associated to that key using a strtr call to replace everything.
 * Example expected format is ( {firstname} => "John") for the formValues array
 * This allows the user to customize the email when sending it to many users and have it dynamically filled in per user.
 * @param string $toAddress The address where we plan to send the email
 * @param string $fromAddress The address we are sending it from (probably noreply)
 * @param string $subject The subject of the email we want to send.
 * @param string $originalBody The generic email data with templated values to be replaced
 * @param array $formValues The associative array containing the template values and their associated replacements.
 *
 * @return resource the PHPMailer object so we can send/parse errors if needed.
 */
function prepareEmail($toAddress, $fromAddress, $subject, $originalBody, $formValues)
{

    $updatedBody = fill_in_template($originalBody, $formValues);

    $mail = new PHPMailer();
    $mail->isSMTP();
    /*$mail->SMTPDebug = 3;
    $mail->Debugoutput = 'html';*/
    $mail->Host = SMTP_SERVER;
    $mail->SMTPAuth = SMTP_AUTH;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    if (SMTP_ENC) {
        $mail->SMTPSecure = SMTP_ENC_TYPE;
    }
    $mail->Port = SMTP_PORT;

    $mail->From = $fromAddress;
    $mail->addAddress($toAddress);
    $mail->Subject = $subject;
    $mail->Body = $updatedBody;
    return $mail;

}

/**
 * This functions job is to take a string, generally a password and return it in a unicode format which Active Directory will accept
 * @param string $passwordInput The original string to transform
 * @return string The formatted password which active directory will accept
 */
function getUnicodePwd($passwordInput)
{
    return mb_convert_encoding("\"" . $passwordInput . "\"", 'utf-16le');
}

/**
 * This function uses the PasswordGenerator library written by Taylor Hornby to attempt to generate mostly
 * secure passwords using the mcrypt library in PHP.
 * @param int $length The length we want the password
 * @param bool $specialChars Whether we want to include special characters
 * @return bool|string Return the password string if we got all the right info, if not return false.
 */
function generatePassword($length, $specialChars)
{
    if ($length && is_numeric($length)) {
        if ($specialChars) {
            return PasswordGenerator::getASCIIPassword($length);
        } else {
            return PasswordGenerator::getAlphaNumericPassword($length);

        }
    } else {
        return false;
    }

}

/**
 * Basic function to help tool conform to DRY principle. Tool assumes email is firstname.lastname@domain
 * Will have to figure out something more dynamic later.
 * @param string $firstName The first part of their email address.
 * @param string $lastName The second part of their email address.
 * @param string $domainName The domain for their email address
 * @return string Properly concatenated email address
 */
function getEmail($firstName, $lastName, $domainName)
{
    return $firstName . "." . $lastName . "@" . $domainName;
}