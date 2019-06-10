<?php
require_once('model.php');
require_once('../functions.php');

/**
 * SpotifyConnector er en klasse der indeholder funktionalitet til at forbinde til Spotifys
 * API og trække data fra den.
 * Attributterne indeholder faste parametre, som definerer Tagifys interaktion med API'et.
 * Alle metoder er static, da der mere er tale om indkapslet funktionalitet end noget, som
 * giver mening at modellere som egentlig objekter.
 *
 * @author      Nikolaj Strands
 * @version     1.0
 * @since       1.0
*/
Class SpotifyConnector {
    
    /**
     * URL som Spotify skal vidersende bruger til efter login.
     */
    private const REDIRECT_URI = 'http://localhost:8888/tagify/src/controllers/login.php';
    
    /**
     * Klient-kode som er en unik kode for appen Tagify, registreret hos Spotify (indsæt).
     */
    private const MY_CLIENT_CODE = '********************************';
    
    /**
     * Klient-hemmelighed, som er endnu en kode for appen hos Spotify, der dog kan ændres
     */
    private const MY_CLIENT_SECRET = '********************************';
    
    /**
     * Kode for de læsesrettigheder brugeren skal godkende ved login hos Spotify
     */
    private const SCOPE = 'user-read-private user-read-email user-library-read';

    
    /* ----------------------------- Metoder ----------------------------- */
    
    /**
     *  Genererer autorisations-URL hos Spotify
     *  
     *  @param $state   tilstandskode der identificerer en forespørgsel
     *  @return         URL som bruger kan viderestilles til for at blive autoriseret
     */  
    public static function getAuthorizationUrl(string $state) : string {
              
        $url = 'https://accounts.spotify.com/authorize' .
          '?response_type=code' .
          '&client_id=' . self::MY_CLIENT_CODE .
          '&scope=' . urlencode(self::SCOPE) .
          '&redirect_uri=' . urlencode(self::REDIRECT_URI) .
          '&state=' . $state;
          
        return $url;
    }
    
    /**
     * Henter access-token hos Spotify
     *
     * @param $authCode     autorisationskode fra Spotify
     * @return              ved succes, access-token, ellers en tom streng
     */  
    public static function authorize(string $authCode) : string {
        
        // Klient-kode og klient-hemmelig skrives sammen
        $base64Codes = base64_encode(self::MY_CLIENT_CODE . ":" . self::MY_CLIENT_SECRET);
        
        // Forespørgsels-array oprettes
        $data = array('grant_type' => 'authorization_code', 'code' => $authCode, 'redirect_uri' => self::REDIRECT_URI);
        
        // cURL-forespørgsel til Spotify oprettes
        $curl = curl_init("https://accounts.spotify.com/api/token");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Authorization: Basic ' . $base64Codes));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        if ($output = curl_exec($curl)) {
            
            return json_decode($output, TRUE)["access_token"];
        
        } else {
            
            return '';
        }
                
    }
    
    /**
     * Henter brugerinformation hos Spotify og downloader brugers profilbillede.
     *
     * @param $accessToken  access-token fra Spotify
     * @return              returnerer et bruger-objekt (uden albums og tags) ved sucess, ellers NULL   
     */  
    public static function fetchUser(string $accessToken) : ?User {
               
        // Lav api-kald efter brugerinfo hos Spotify vha. cURL
        $curl = curl_init("https://api.spotify.com/v1/me");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        if ($result = curl_exec($curl)) {
            
            $userInfo = json_decode($result, TRUE);

            if ($userInfo['images'][0]['url']) {
                
                $imageUrl = 'images/' . $userInfo["id"] . '.jpeg';
                
                // Brugers profilbillede hentes ned i en mappe, hvis det findes (se functions.php)
                grab_image($userInfo['images'][0]['url'], '../' . $imageUrl);
                
            } else {
                
                $imageUrl = 'images/default.png';
            }
        
            // Bruger-objekt oprettes og returneres            
            $user = new User($userInfo["id"], $userInfo['display_name'], $imageUrl);
            return $user;
            
        } else {
            
            // Ved fejl, eturneres NULL
            return NULL;
        }
        
    }
    
    /**
     * Henter albumlisten for en bruger hos Spotify
     * 
     * @param $accessToken  access-token fra Spotify
     * @return              et array af album-objekter ved success, ellers NULL
     */ 
    public static function fetchAlbums(string $accessToken) : ?array {
        
        $albumList = [];
        
        // Start-URL
        $nextURL = "https://api.spotify.com/v1/me/albums?offset=0&limit=20";
        
        // Brugers albums hentes hos Spotify vha. en eller flere cURL-forespørgsler
        do {        
            $curl = curl_init($nextURL);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);     
            
            if($result = curl_exec($curl)) {
                
                $json = json_decode($result, TRUE);
                
                // Angiver næste URL ved uhentede albums (ellers NULL)
                $nextURL = $json["next"];
                $spotifyAlbums = $json["items"];
        
                // De returnerede albums løbes igennem
                foreach($spotifyAlbums as $item) {
    
                    // Kunstnere samles til semikolonsepareret streng    
                    $artistNameArray = [];
                    foreach($item['album'][artists] as $artist){
                        $artistNameArray[]  = $artist[name];           
                    }
                    $artists = implode("; ", $artistNameArray);
                    
                    // Et album-objekt oprettes og tilføjes til brugerens liste
                    $album = new Album($item['album'][id], $item['album'][images][1][url], $item['album'][name], $artists); 
                    $albumList[] = $album;      
                }
                        
            } else {
                // Ved fejl forlades løkken og album-listen sættes til NULL
                $nextURL = NULL;
                $AlbumList = NULL;
            }
           
         } while($nextURL != NULL);
         
         return $albumList;
    }
}