<?php

echo <<<_END
<html><head><title>Upload File to Check for Virus:</title></head><body>
<form method = 'post' action='User.php' enctype = 'multipart/form-data'>
     Full Name: <input type = 'text' name = 'name'>
     Enter Username: <input type = 'text' name = 'username'>
     Enter Password: <input type = 'text' name = 'password'>
    <button type='text' name = 'register'>Register</button></br></br>
</body>
</html>
_END;

require 'helper.php';


if (isset($_POST['register'])) {
    uploadUser($conn);
}
$conn->close();


function uploadUser($conn) {
    if(isset($_POST['name']) && isset($_POST['username']) && isset($_POST['password'])){
        $name = $_POST['name']; 
        $username = $_POST['username'];
            $salt1 = random_bytes(5);
            $salt2 = random_bytes(5); 
            $hashedPW = hash('ripemd128', $salt1 . $_POST['password'] . $salt2);
            $query = "INSERT INTO Users (Name, Username, HashedPW, PreSalt, PostSalt)
                VALUES"."('$name', '$username', '$hashedPW', '$salt1', '$salt2')";
            $result = $conn->query($query); //returns TRUE if INSERT successful or FALSE
            if($result) {
                echo "User registered successfully!";
                $_SESSION['fullname'] = $name;
                $_SESSION['username'] = $username; 
                $_SESSION['password'] = $hashedPW;
                $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] .
                            $_SERVER['HTTP_USER_AGENT']); 
                $_SESSION['initiated'] = 0;
                header('location: upload.php'); 
            }
            else 
                echo "Username exists already! Please try again."; 
    }
}
/*
function checkUnique($conn, $username){
    $userInfo = array();
    $query2 = "SELECT * FROM User";
    $result = $conn->query($query2); // returns object if SELECT successful or FALSE
    if (!$result) // must check FALSE
        echo "no result";
        else {
            $rows = $result->num_rows;
            if ($rows > 0) {
                for ($j = 0; $j < $rows; ++ $j) {
                    $result->data_seek($j);
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    array_push($userInfo, $row['Username']);
                }
            }
            echo "here"; 
            $result->close();
        }
        $unique = true;
        for($i = 0; $i < count($userInfo); $i++){
            if($username == $userInfo[$i])
                $unique = false; 
        }
        return $unique;
}
*/



?>