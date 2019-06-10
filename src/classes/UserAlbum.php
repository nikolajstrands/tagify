<?php
require_once('Tag.php');
require_once('Album.php');

/**
 * Domæneklasse der repræsenterer en brugers album inkl. tags
 *
 * Attributterne indeholder et databasegenereret id, et album-objekt (der kan deles med andre)
 * samt en liste af tag-objekter.
 *
 * Klassen har ingen metoder.
 *
 * @author      Nikolaj Strands
 * @version     1.0
 * @since       1.0
*/
Class UserAlbum {
    /**
     * Album-id og og album-objekt
     */
    public $userAlbumId = "";
    public $album;
    
    /**
     * Liste af tag-objekter
     */
    public $tagList = [];
    
    /**
     * Constructor
     */
    function __construct($userAlbumId, Album $album){
        
        $this->userAlbumId = $userAlbumId;
        $this->album = $album;   
    }
}

?>