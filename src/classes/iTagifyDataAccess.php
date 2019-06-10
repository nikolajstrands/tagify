<?php

/**
 * Dette interfacer abstraherer den nødvendige udveksling mellem Tagify og et persistent
 * datalag for domæneklasserne.
 *
 * Der gøres ingen antagelser om datalagets fysiske og logiske placering og form
 * (fx XML-fil, MySQL- eller NoSQL-database, lokal eller netværksbaseret storage).
 *
 * @author    Nikolaj Strands
 * @version   1.0
 * @since     1.0
 */
interface iTagifyDataAccess {
    
    // Opretter hhv. tags, albums og brugere i datalaget (eller sikrer de er oprettet)
    public function createTag(string $name) : Tag;
    public function createAlbum(Album $album) : Album;
    public function createUser(string $userId, string $userDisplayName, string $userImgUrl) : bool;
    
    // Henter et komplet bruger-objekt (NULL hvis det ikke findes)
    public function getUser(string $userId) : ?User;

    // Hhv. fjerner og tilføjer tag til et brugeralbum
    public function addUserAlbumTag(string $tagName, int $userAlbumId) : ?Tag;
    public function removeUserAlbumTag(int $tagId, int $userAlbumId) : bool;
    
    // Synkroniserer brugerens albums i datalaget med et array af albums
    public function syncSpotifyAlbums(string $userId, array $spotifyAlbumList) : bool;
}

?>