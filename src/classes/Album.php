<?php

/**
 * Domæneklasse der repræsenterer et album
 *
 * Attributterne indeholder album-id, albumcover-URL, album-titel og
 * album-kunstner. De tre første er direkte taget fra Spotifys datamodel,
 * den sidste en streng-repræsentation af Spotifys mere udfoldede håndtering
 * af kunstere på et album.
 *
 * Klassen har ingen metoder.
 *
 * @author      Nikolaj Strands
 * @version     1.0
 * @since       1.0
*/
Class Album {
    
    /**
     * Attributter fra Spotify
     */
    public $albumId = "";
    public $albumImgUrl = "";
    public $albumTitle = "";
    
    /**
     * Streng-repræsentation af kunstnere
     */
    public $albumArtist = "";
  
    /**
     * Constructor
     */
    function __construct($albumId, $albumImgUrl, $albumTitle, $albumArtist){
        
        $this->albumId = $albumId;
        $this->albumImgUrl = $albumImgUrl;
        $this->albumTitle = $albumTitle;
        $this->albumArtist = $albumArtist;
       
    }
}
?>