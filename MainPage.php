<?php
session_start();
if (!isset($_SESSION['initiated']))
{
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}

# ini_set('session.gc_maxlifetime', 60 * 60 * 24);
# ini_set('session.save_path', '/home/user/myaccount/sessions');
echo <<<_END
<html>
<body>
<form method = 'post' action='MainPage.php' enctype = 'multipart/form-data'>
            <h1>Welcome to Ari's Dream!</h1>
            <input type ='submit' value = 'User Login' name = 'login' style='float: right'>      
</body>
</html>
_END;

require 'helper.php';

if (isset($_SESSION['username'])) 
    destroy_session_and_data();

//authenticating user: http auth
if (isset($_POST['login'])) 
    authenticateUser($conn);
storeData($conn); 

$conn->close();
    
    function newUser()
    {
        header('Location: User.php');
    }

    function authenticateUser($conn)
    {
        $names = array();
        $usernames = array();
        $hashedPW = array();
        $preSalt = array();
        $postSalt = array();
        $query2 = "SELECT * FROM Users";
        $result = $conn->query($query2); // returns object if SELECT successful or FALSE
        if (! $result) // must check FALSE
            mysql_fatal_error();
            else {
                $rows = $result->num_rows;
                if ($rows > 0) {
                    for ($j = 0; $j < $rows; ++ $j) {
                        $result->data_seek($j);
                        $row = $result->fetch_array(MYSQLI_ASSOC);
                        array_push($names, $row['Name']);
                        array_push($usernames, $row['Username']);
                        array_push($hashedPW, $row['HashedPW']);
                        array_push($preSalt, $row['PreSalt']);
                        array_push($postSalt, $row['PostSalt']);
                    }
                }
            }
        $result->close();

            if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
                $tmpUser = sanitizeMySQL($conn, $_SERVER['PHP_AUTH_USER']);
                $tmpPassword = sanitizeMySQL($conn, $_SERVER['PHP_AUTH_PW']);
                for ($j = 0; $j < count($usernames); ++$j) {
                    if($tmpUser == $usernames[$j]) {
                        $givenPW = hash('ripemd128', $preSalt[$j] . $tmpPassword . $postSalt[$j]);
                        if ($givenPW == $hashedPW[$j]) {
                            echo "You are now logged in";
                            $_SESSION['fullname'] = $names[$j];
                            $_SESSION['username'] = $tmpUser; //checked at top of both files
                            $_SESSION['password'] = $givenPW; 
                            $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] .
                                        $_SERVER['HTTP_USER_AGENT']);  //check in upload.php
                        }
                        else{
                            die('Invalid credentials. Please exit the page and try again.');
                        }
                    }
                }
                //arbitrarily define session
                $_SESSION['initiated'] = 0; 
                header('location: Upload.php');
            }
            else{
                header('WWW-Authenticate: Basic realm="Restricted Section"');
                header('HTTP/1.0 401 Unauthorized');
                die("Please enter your username and password");
            }
    }
    
    function storeData($conn){
        $poemArr = array(); 
        $query1 = "SELECT DISTINCT poem FROM displayData"; 
        $result = $conn->query($query1); // returns object if SELECT successful or FALSE
        if (! $result) // must check FALSE
            mysql_fatal_error();
            else {
                $rows = $result->num_rows;
                if ($rows > 0) {
                    for ($j = 0; $j < $rows; ++ $j) {
                        $result->data_seek($j);
                        $row = $result->fetch_array(MYSQLI_ASSOC);
                        array_push($poemArr, $row['poem']);
                    }
                }
            }
            $result->close();
            displayData($conn, $poemArr);
    }
    
    //want to display most recent information first
    function displayData($conn, $poemArr){
        for ($i = 0; $i < count($poemArr); ++ $i) {
            $handle = fopen($poemArr[$i], 'r'); 
            echo '<br>' . str_replace('.txt', "", $poemArr[$i]) . '<br>';  
            echo fread($handle, filesize($poemArr[$i]));
            //echo '<br><br>'.$poemArr[$i];
            $query2 = "SELECT image FROM displayData where poem = ". "'$poemArr[$i]'";
            $result = $conn->query($query2); // returns object if SELECT successful or FALSE
            if (! $result) // must check FALSE
                mysql_fatal_error();
                else {
                    $rows = $result->num_rows;
                    if ($rows > 0) {
                        $imageArr = array(); 
                        for ($j = 0; $j < $rows; ++ $j) {
                            $result->data_seek($j);
                            $row = $result->fetch_array(MYSQLI_ASSOC);
                            array_push($imageArr, $row['image']);
                        }
                    }
                    echo '<br>';
                    for ($j = 0; $j < count($imageArr); ++ $j){
                        echo "<img src={$imageArr[$j]}>";
                    }
                }
                $result->close();
        }
    }
    
                
    ?>