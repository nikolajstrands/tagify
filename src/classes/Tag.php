<?php

/**
 * Domæneklasse der repræsenterer et tag (der kan deles mellem brugere).
 *
 * Attributterne indeholder et databasegenereret id samt et tag-navn.
 *
 * Klassen har ingen metoder.
 *
 * @author      Nikolaj Strands
 * @version     1.0
 * @since       1.0
*/
Class Tag {
    
    /**
     * Tag-id og tag-navn
     */
    public $tagId;
    public $tagName;
    
    /**
     * Constructor
     */
    function __construct( int $tagId, string $tagName) {
        
            $this->tagId = $tagId;
            $this->tagName = $tagName;   
    }
}
?>