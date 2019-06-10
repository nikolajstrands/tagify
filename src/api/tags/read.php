<?php
session_start();
require_once('../../classes/Database.php');
require_once('../../classes/model.php');
require_once('../../functions.php');


        if($error = loginError()){
               
               $response = $error;
           
           } else {
               
                $db = new Database();
                
                $user = $db->getUser($_SESSION['userId']);
                
                $tags = $user->getUniqueTags();
                
                $response = $tags;
        
               
           }
           
           header('Content-Type: application/json');
           echo json_encode($response);

?>