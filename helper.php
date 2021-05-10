<?php
/*
 * helper.php contains helper functions that are required 
 * across various php pages to prevent redundancy and ensure
 * correct session management.
 */
// Connect to Database
// login.php contains MySQL code that must be implemented in MySQL console in advance.
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db, $port);

if ($conn->connect_error)
    return mysql_fatal_error();
    
    // handle connection and query errors
    function mysql_fatal_error()
    {
        echo <<<_END
       WHOOPS! We apologize for the delay, but this request cannot be
       processed. Please try again.<br>
    _END;
    }
    
    
    function fileHandler($var)
    {
        $var = strtolower(preg_replace("[^A-Za-z0-9.]", "", $var));
        $var = file_get_contents($var);
        return $var;
    }
    
    
    // sanitize text information from user
    function sanitizeString($var)
    {
        $var = stripslashes($var);
        $var = strip_tags($var);
        $var = htmlentities($var);
        return $var;
    }
    
    function sanitizeMySQL($conn, $var)
    {
        $var = $conn->real_escape_string($var);
        $var = sanitizeString($var);
        return $var;
    }
    
    function different_user(){
        destroy_session_and_data();
        echo 'Please log in!';
    }
    
    function destroy_session_and_data() {
        $_SESSION = array();
        setcookie(session_name(), '', time() - 2592000, '/');
        session_destroy();
    }
    
