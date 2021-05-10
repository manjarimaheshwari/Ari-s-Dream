<?php
session_start(); 
if (!isset($_SESSION['initiated']))
{
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}
if (isset($_SESSION['username'])){
    $fullname = $_SESSION['fullname'];
    echo <<<_END
        Welcome, $fullname! Enter a poem below. 
        <form id = 'uploadform' method = 'post' action = 'Upload.php' enctype = 'multipart/form-data'>
            Input Poem Name: <input type = 'text' name = 'poemtitle'> <br>
            <textarea rows='15' cols = '75' name = 'poem' form = 'uploadform'></textarea><br><br> 
        
            Upload one or more pictures: <br>
            <input type = 'file' name = picture[] size= '10' accept = 'image/jpeg' multiple> <br> 
 
            <button type='submit' name = 'upload'>Upload</button><br>
            <button type='submit' name = 'main' style = 'float: right'> Go Back to Main Page!</button>
_END;
}
require 'helper.php';
if ($_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']))
    different_user();

if (isset($_POST['upload'])) {
    adminUpload($conn);
}

if (isset($_POST['main'])) {
    header('location: MainPage.php'); 
}

$conn->close();
    
    
    function adminUpload($conn)
    {
        if ($_POST['poem'] && $_POST['poemtitle']) {
            $poem = str_replace("\r\n", ' ', $_POST['poem']); 
            $poem = sanitizeMySQL($conn, $poem);
            if (strlen($_POST['poemtitle']) > 70) die ("Poem title is too long. Please try again.");
            $poemTitle = sanitizeMySQL($conn, $_POST['poemtitle']); 
            $poem_file =  $poemTitle.'.txt';
            $handle = fopen($poem_file, 'w') or die ('Cannot read poem. Please try again.'); 
            fwrite($handle, $poem); 
        
            $total = count($_FILES['picture']['name']);
            $data = array(); 
            if(!$_FILES['picture']['name'][0]){
                echo 'Please upload at least one image!';
            }
            else {        
                for ($i=0; $i < $total; $i++)
                {
                    //get tmp file path
                    $filename = $_FILES['picture']['tmp_name'][$i];               
                    $target_dir = "C:/wamp/www/Ari's Dream/"; 
                    $file = strtolower(
                         preg_replace("/[^A-Za-z0-9.]/", "", $_FILES['picture']['name'][$i]));
                    $target_file = $target_dir . basename($file); 
               
                    if ($filename != "") {
                        if (move_uploaded_file($filename, $target_file)) {
                        echo "The file ". $file. " has been uploaded.<br>";
                        array_push($data, $file);
                        }
                        else {
                            echo 'error uploading!';
                        }    
                   }
                }
            sqlSubmit($conn, $poem_file, $data); 
           }
        }
    
        else {
            echo "You must include a poem and its title!";
    }
   }
    
    function sqlSubmit($conn, $poem, $data){
        for ($i=0; $i < count($data); $i++)
        {
            $query = "INSERT INTO displayData (poem, image)
                     VALUES" . "('$poem', '$data[$i]')";
            $result = $conn->query($query); // returns TRUE if INSERT successful or FALSE
            if ($result) {
                $j = $i + 1; 
                echo '</br>Submission of file '.$j.' successful!';
            }   else 
                    mysql_fatal_error();
            }
        }
    
    
    ?>