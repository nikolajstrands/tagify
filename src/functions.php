<?php

/* Funktion der returnerer fejl, hvis bruger enten ikke er
 * autoriseret eller ikke har synkroniseret bibliotek (ud fra
 * session-variable). Hvis der ikke er problemer returneres FALSE
 */

function loginError() { 
 
    if(!$_SESSION['isLoggedin']) {
    
        return [ "error" => "Not Authorized" ];
    
        
    } else if(!$_SESSION['isSyncronized']) {
        
        return [ "error" => "Not Syncronized"];
        
    
    } else {
        
        return FALSE;
    }
    
}

/* Funktion der henter et billede fra url vha. cURL og gemmer det. */

function grab_image($url,$saveto){
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    
    $raw = curl_exec($ch);
    
    curl_close($ch);
    
    if(file_exists($saveto)){
        
        unlink($saveto);
    }
    
    $fp = fopen($saveto,'x');
    fwrite($fp, $raw);
    fclose($fp);
}

 // Funktion der genererer tilfældig streng (inspireret fra Spotify)
  
  function generateRandomString($length) {
          $text = "";
          $possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
          
          for($i = 0; $i < $length; $i++) {
            $text .= $possible[floor(rand(0, 100) / 100 * strlen($possible))];
          }
          return $text;
        }
