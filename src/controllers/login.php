<?php
session_start();
require_once('../classes/SpotifyConnector.php');
require_once('../functions.php');

// Hvis dette er et callback fra Spotify
if(($authCode = $_GET["code"]) && ($returnState = $_GET["state"])) {
  
  // Er state-værdien ens?
  if ($returnState == $_SESSION['state']) {
    
    // Hent access-token hos Spotify
    if( $accesToken = SpotifyConnector::authorize($authCode, $returnState)) {
      
      // Gem access-token i session-variabel
      $_SESSION['access_token'] = $accesToken;
      // Sæt logget ind-variabel i session
      $_SESSION['isLoggedin'] = true;
      
      // Viderestil til app
      header(  'Location: ../index.html'); 
      exit();    
      
    } else {
      
      echo "Fejl: Kunne ikke hente access-token hos Spotify. Prøv igen senere.";
    }
    
  } else {
    
    echo "Fejl: Tilstands-id for forespørgsler til Spotify stemmmer ikke overens. Prøv igen senere.";
  }
  
  
// Hvis det ikke er et callback fra Spotify
} else {
  
  // Kræver det noget at gå videre her?

  // En tilfældig 16-cifret kode (der identificere denne forespørgsel om autorisation hos Spotify generes)
  $state = generateRandomString(16);
  
  // Den gemmes i en session-variable
  $_SESSION['state'] = $state;

  // En autorisations-URL genereres
  $url = SpotifyConnector::getAuthorizationUrl($state);
  
  // Bruger viderestilles til autorisation hos Spotify
  header('Location: ' . $url);
  exit();
  
}
?>