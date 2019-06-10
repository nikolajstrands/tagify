<?php
session_start();
require_once('../../classes/Database.php');
require_once('../../classes/User.php');
require_once('../../functions.php');

    
    if($error = loginError()){
        
        $response = $error;
    
    } else {
        
        $database = new Database();
        
        $user = $database->getUser($_SESSION['userId']);
        
        $response = $user->userAlbumList;
        
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);

?>