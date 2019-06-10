<?php
session_start();
require_once('../../classes/Database.php');
require_once('../../classes/model.php');
require_once('../../functions.php');

if($error = loginError()){
    
    $response = $error;
    
} else {
        
    $name = $_POST['name'];
    $user = $_SESSION['userId'];
    $userAlbumId = $_POST['useralbumid'];
    
    if(empty($name)){
        
        // Tag-navnet er tomt
        $response = [ "error" => "Empty tag"];
              
    } else {
    
        $db = new Database();
        $response = $db->addUserAlbumTag($name, $userAlbumId);
        
        // Hvis tagget findes i forvejen, og metoden returnerer NULL
        if(is_null($response)){
            
            $response = [ "error" => "Tag duplicate"];
        }
    }

}
    
header('Content-Type: application/json');
echo json_encode($response);

?>