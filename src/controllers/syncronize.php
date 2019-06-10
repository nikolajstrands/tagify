<?php
session_start();
require_once('../classes/Database.php');
require_once('../classes/model.php');
require_once('../classes/SpotifyConnector.php');

// Hvis bruger ikke er logget ind, returneres fejl
if(!$_SESSION['isLoggedin']) {
    
        $response = [ "error" => "Not Authorized" ];
        
} else {
        
        $accessToken = $_SESSION['access_token'];

        // Bruger og albumliste hentes fra Spotify
        $user = SpotifyConnector::fetchUser($accessToken);
        $albums = SpotifyConnector::fetchAlbums($accessToken);
        
        // En databaseforbindelse oprettes
        $database = new Database();
        
        if(!$database->getUser($user->userId)){
            
            // Bruger findes ikke i databasen, og må oprettes
            $database->createUser($user->userId, $user->userDisplayName, $user->userImgUrl);      
        }
        
        // Synkroniser med Spotify-albums med databasen
        $database->syncSpotifyAlbums($user->userId, $albums);
        
        // Sæt session-varaible
        $_SESSION["isSyncronized"] = 1;
        $_SESSION['userId'] = $user->userId;
        
        $response = ["ok" => "Library syncronized" ];
    }
    
echo json_encode($response);

?>