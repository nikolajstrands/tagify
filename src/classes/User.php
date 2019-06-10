<?php
require_once('UserAlbum.php');

/**
 * User er en domæneklasse der repræsenterer en bruger af Tagify-appen
 *
 * Attributterne indeholder et bruger-id, som er defineret af Spotify, et visningsnavn for
 * brugeren, som kan ændres via brugerens Spotify-konto, en lokal URL for brugerens
 * profilbillede samt en liste over brugerens brugeralbumobjekter.
 *
 * Klassen har to metoder, der returnere data som ikke direkte fremgår af atttributterne.
 *
 * @author      Nikolaj Strands
 * @version     1.0
 * @since       1.0
*/
Class User {
    
    /**
     * Attributter for brugeren
     */
    public $userId = "";
    public $userDisplayName = "";
    public $userImgUrl = "";
    
    /**
     * Brugerens bibliotek af brugeralbums
     */
    public $userAlbumList = [];
    
    /**
     * Constructor
     */
    function __construct($userId, $userDisplayName, $userImgUrl) {
        
        $this->userId = $userId;
        $this->userDisplayName = $userDisplayName;
        $this->userImgUrl = $userImgUrl; 
    }
    
    /**
     *  Henter en liste med brugerens unikke tags
     *  
     *  @return     Liste over unikke tags på brugeralbums i brugerens bibliotek
     */  
    public function getUniqueTags() {
        
        $tagList = [];
        
        foreach ($this->userAlbumList as $userAlbum) {
            
            foreach($userAlbum->tagList as $tag){
                
                $index = array_search($tag, $tagList);
                if ($index === FALSE){
                    $tagList[] = $tag;
                }  
            }           
        }
        
        return $tagList;
    }
    
    /**
     *  Henter liste over albums fra brugens liste af brugeralbums
     *  
     *  @return         Liste af albums fra brugerens brugeralbumliste
     */  
    public function getAlbumList() {
        
        $albumList = [];
        foreach ($this->userAlbumList as $userAlbum) {
            $albumList[] = $userAlbum->album;
        }
        
        return $albumList;
    }
    
}


?>