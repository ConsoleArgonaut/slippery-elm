<?php

session_start();

if(isset($_POST['username'])){
    $elm_username = $_POST['username'];

    $query = "select * from `elm_users` where username='".$elm_username."'";

    $result = mysqli_query($conn,$query);
    if($result == null){
        return false;
    }

    while($row = mysqli_fetch_array($result)){
        if($row['password'] == md5($_POST['password'])){
            $_SESSION['loggedIn'] = "true";
            header('Location: ../index.php');
        }else{
            header('Location: ../index.php');
            echo "Loggin fail";
        }
    }
}
?>