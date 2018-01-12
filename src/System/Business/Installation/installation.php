<?php

$elm_Installation_UserGotCreated = false;

if(isset($_POST['elm_Post_Username']) && isset($_POST['elm_Post_Password']) && isset($_POST['elm_Post_Email'])) {
    //Stripslashes from variables
    $elm_Post_Username = stripslashes($_POST['elm_Post_Username']);
    $elm_Post_Password = stripslashes($_POST['elm_Post_Password']);
    $elm_Post_Email = stripslashes($_POST['elm_Post_Email']);

    //Check if all variables have a value
    if ($elm_Post_Username != '' && $elm_Post_Password != '' && $elm_Post_Email != '') {
        //Create Database
        if (!elm_Data_GetIsDbInitialized())
            elm_Data_InitializeDb();
        //Create User and Create Database
        if (elm_Data_CreateUser($elm_Post_Username, $elm_Post_Password, $elm_Post_Email, elm_Data_GetRoleId('admin')))
            $elm_Installation_UserGotCreated = true;

    }
}
if($elm_Installation_UserGotCreated) {
    //Refreshes page so page content can get loaded
    header("Refresh:0");
}
else {
    include_once("System/UI/HTML/adminCreation.html");
}

?>