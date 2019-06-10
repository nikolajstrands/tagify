<?php
session_start();
require_once('../../classes/Database.php');
require_once('../../classes/model.php');
require_once('../../functions.php');

if($error = loginError()){
               
    $response = $error;

} else {
    
    $tagId = $_GET['tagId'];
    $userAlbumId = $_GET['userAlbumId'];
    
    if(!empty($tagId) && !empty($userAlbumId)){
        
        $db = new Database();     

        if($res = $db->removeUserAlbumTag($tagId, $userAlbumId)) {
            
            $response = [ "ok" => "A tag was deleted"];
            
        } else {
            
            $response = [ "error" => "No tag was deleted"];
        }
               
    } else {
        
        $response = [ "error" => "No tag or album specified"];
    }
    
    
   
}

header('Content-Type: application/json');
echo json_encode($response);

?>