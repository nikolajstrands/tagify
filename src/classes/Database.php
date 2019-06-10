<?php
require_once('model.php');
require_once('iTagifyDataAccess.php');

/**
 * Klassen implementerer al funktionalitet for Tagifys kommunikation med MySQL-databasen.
 *
 * Interfacet iTagifyDataAccess kodificerer i mere abstrakt form, hvilken udveksling med det
 * persistent datalag, appen har brug for. Og Database-klassen implementerer dette.
 *
 * Udover at implementerer metoderne fra interfacet har klassen to private hjælpemetoder.
 *
 * @author    Nikolaj Strands
 * @version   1.0
 * @since     1.0
 */
class Database implements iTagifyDataAccess {
    
    /**
     * Databaseoplysninger (indsæt)
     */
    private $hostname = '';
    private $username = '';
    private $password = '';
    private $database = '';
    
    /**
     * Et dataforbindelsesobjekt
     */
    private $conn;
        
    /**
     * Constructor
     */
    function __construct() {
        
        // Her skabes forbindelse til databasen
        $this->conn = new mysqli($this->hostname, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) die("Fejl: Kan ikke etablere forbindelse til databasen ...");
    }
    
    /**
     * Destructor
     */
    function __destruct() {
        
        // Databaseforbindelsen lukkes
        $this->conn->close();
    }
    
    /* ----------------------------- metoder fra iTagifyDataAccess-interface -------------------------------- */
    
    /**
     * Opretter et tag i tag-tabellen
     *
     * @param $tagName  taggets navn
     * @return          et tag-objekt (nyt eller gammelt)
     */
    public function createTag(string $tagName) : Tag  {
        
        // Forsøg på XSS forhindres
        $tagName = htmlentities($tagName);
        
        // Tagget slås op i databasen 
        $query = 'SELECT tag_id, tag_name FROM tags WHERE tag_name=?';
        if($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('s', $tagName);
            
            $stmt->execute();
            $stmt->bind_result($id, $name);
            $result = $stmt->fetch();
            $stmt->close();
            
            if(!$result) { 
                // Tagget findes ikke
                if($stmt = $this->conn->prepare('INSERT INTO tags (tag_name) VALUES (?)')){
                    $stmt->bind_param('s', $tagName);
                    $stmt->execute();
                    $stmt->close();
                    $tagId = $this->conn->insert_id;
    
                    $tag = new Tag($tagId, $tagName);
                    return $tag;                       
                } else {
                      die("Fejl: Kunne ikke indsætte tag i databasen.");
                }
            } else {
                // Tagget findes
                $tag = new Tag($id, $name);
                return $tag;       
            }
              
        } else {
            die("Fejl: Kunne ikke hente taginfo fra databasen.");
        }     
    }
    
    /**
     * Opretter et album i album-tabellen
     *
     * Metoden kan tage et album-objekt som parameter, der der ikke bruges id med autoincrement i tabellen. 
     *
     * @param $album    et album-objekt
     * @return          album-objektet
     */
    public function createAlbum(Album $album) : Album {
          
        // Exception hvis null eller et 'tomt' album?
        
        // Albummet slås op i databasen
        $query = "SELECT album_id FROM albums WHERE album_id=?";
        if ( $stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('s', $album->albumId );
            
            $stmt->execute();
            $stmt->bind_result($albumId);
            $result = $stmt->fetch();
            $stmt->close();
            
            if(!$result) {
                // Albummet findes ikke
                if($stmt = $this->conn->prepare('INSERT INTO albums VALUES (?, ?, ?, ?)')){
                    $stmt->bind_param('ssss', $album->albumId,  $album->albumImgUrl,  $album->albumTitle, $album->albumArtist);
                    $stmt->execute();
                    $stmt->close();

                    return $album;                       
                } else {
                      die("Fejl: Kunne ikke indsætte album i databasen.");
                }
                
            } else {
                // Albummet findes
                return $album;
            }
           
        } else {
            die("Fejl: Kunne ikke hente albuminfo fra databasen.");
        }
    }
    
    /**
     *  Opretter en ny bruger i databasen.
     *
     * @param $userId           et Spotify-bruger-id 
     * @param $userDisplayName  et visningsnavn for bruger  
     * @param $userImgUrl       en url til profilbillede
     * @return                  TRUE hvis brugerens oprettes, ellers FALSE
     *
     */
    public function createUser(string $userId, string $userDisplayName, string $userImgUrl) : bool {
        
        // Forsøg på XSS forhindres
        $userId = htmlentities($userId);
        $userDisplayName  = htmlentities($userDisplayName);
        $userImgUrl = htmlentities($userImgUrl);
        
        if(!$this->getUser($userId)){
            // bruger findes ikke allerede
            
            if($stmt = $this->conn->prepare('INSERT INTO users (user_id, user_display_name, user_img_url) VALUES (?, ?, ?)')){
                $stmt->bind_param('sss', $userId, $userDisplayName, $userImgUrl);
                $stmt->execute();
                $stmt->close();

                return TRUE;                       
            } else {
                  die("Fejl: Kunne ikke indsætte tag i databasen.");
            }                       
        } else {
            return FALSE;
        }
        
    }
    
     /**
     * Henter det fulde brugerobjekt (inkl. albums med tags) i databasen.
     *
     * Der bruges prepared statements i første del, hvor input kommer udefra, men ikke i resten.
     *
     * @param $userId   et Spotify-bruger-id
     * @return          Et brugerobjekt hvis bruger-id'et findes i databasen, ellers NULL.           
     */
    public function getUser(string $userId) : ?User {
        
        // Forsøg på XSS forhindres
        $userId = htmlentities($userId);
          
        // Her hentes navn og billed-url fra databasen og bruger-objektet oprettes      
        $query = "SELECT * FROM users WHERE user_id=?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $stmt->bind_result($uId, $uDisplayName, $uImgUrl);
            
            $result = $stmt->fetch();
            $stmt->close();
            
            if($result) {
                
                // Bruger-objekt oprettes
                $user = new User($uId, $uDisplayName, $uImgUrl);
                
                 // Hent brugeralbums og albums fra databasen
                $query = "SELECT user_album_id, albums.album_id, album_img_url,  album_title, album_artist  FROM user_albums INNER JOIN albums ON user_albums.album_id = albums.album_id WHERE user_id='$user->userId'";   
                $result = $this->conn->query($query);   
                if(!$result) die("Fejl: kan ikke hente albuminfo i databasen");
          
                // Løb brugeralbums fra databasen igennem
                while($row = $result->fetch_assoc()) {
                    
                    $album = new Album($row["album_id"], $row["album_img_url"], $row["album_title"], $row["album_artist"]);
        
                    $userAlbum = new UserAlbum($row["user_album_id"], $album);
                              
                    // Hent tags for det givne brugeralbum
                    $query = "SELECT tags.tag_id, tag_name FROM tag_mappings INNER JOIN tags ON tag_mappings.tag_id = tags.tag_id WHERE user_album_id='$userAlbum->userAlbumId'";   
                    $result2 = $this->conn->query($query);
                    if(!$result2) die("Fejl");
                    
                    
                    while($record = $result2->fetch_assoc()) {
                        
                        $tag = new Tag($record["tag_id"], $record["tag_name"]);
                        
                        $userAlbum->tagList[] = $tag;
                        
                    }
        
                    $user->userAlbumList[] = $userAlbum;
                    
                }

                return $user; 
                
                
                
            } else {
                // Brugeren findes ikke i databasen
                return NULL;
            }
                                  
        } else {
            die("Fejl: kunne ikke hente brugerinfo i databasen");
        }              
             
    }
     
    /**
     * Tilføjer et tag til et bestemt (eksisterende) brugeralbum
     *
     * @param $tagName      et tag-navn
     * @param $userAlbumId  id for det brugeralbum tagget til skal tilføjes til
     * @return              tagget returneres, hvis det ikke findes i forvejen, ellers NULL
     */
    public function addUserAlbumTag(string $tagName, int $userAlbumId) : ?Tag {
        
        // Forsøg på XSS forhindres
        $tagName = htmlentities($tagName);
        $userAlbumId = htmlentities($userAlbumId);
        
        // Vi sikrer os at tagget findes
        $tag = $this->createTag($tagName);

        // Vi undersøger om brugeralbummet allerede har tagget
        $query = "SELECT * FROM tag_mappings WHERE user_album_id=? AND tag_id=?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ii', $userAlbumId, $tag->tagId);
            $stmt->execute();
            $stmt->bind_result($uaId, $aId);
            $result = $stmt->fetch();
            $stmt->close();
            
            if($result){
                // Brugeralbummet har allerede tagget
                return NULL;  
            } else {
                 // Tagget findes ikke på brugeralbummet
                 $query = "INSERT INTO tag_mappings VALUES (?, ?)";
                 if ( $stmt = $this->conn->prepare($query)) {
                    $stmt->bind_param('ii', $userAlbumId, $tag->tagId);
                    $stmt->execute();
                    $stmt->close();
                    
                    return $tag;
                    
                 } else {
                    die("Fejl: Kunne ikke tilføje tag til brugeralbum i databasen.");
                 }
            }               
        } else {
            die("Fejl: kunne ikke hente brugeralbum-info fra databasen.");
        }      
    
    }
 
    /**
     * Fjerner tag fra et brugeralbum
     *
     * @param $tagId        id for tag, der skal tilføjes
     * @param $userAlbumId  id for brugeralbum, tagget skal tilføjes til
     * @return              hvis der blev slettet et tag, returneres TRUE, ellers FALSE
     */
    public function removeUserAlbumTag(int $tagId, int $userAlbumId) : bool {
        
        $query = "DELETE FROM tag_mappings WHERE user_album_id=? AND tag_id=?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ii', $userAlbumId, $tagId);
            $stmt->execute();
            $success = $stmt->affected_rows;
            $stmt->close();
            
            if(!$success) {
                return FALSE;
            } else {
                return TRUE;
            }
            
        } else {
            die("Fejl: kunne ikke slette tag fra brugeralbum i databasen.");
            
        }      
    }
    
    /**
     * Synkroniserer en brugers albums i databasen med et album-array (typisk fra Spotify)
     *
     * Albums i arrayet oprettes, hvis de ikke findes og tilføjes til brugeren,
     * brugeralbums i databasen, som ikke findes i Spotify-arrayet slettes.
     *
     * @param $userId               et bruger-id
     * @param $spotifyAlbumList     et array af albumobjekter
     * @return                      TRUE ved succes, FALSE hvis bruger ikke findes
     */
    public function syncSpotifyAlbums(string $userId, array $spotifyAlbumList) : bool {
        
        
        // Tjek om brugeren findes
        if($user = $this->getUser($userId)){
            
            $userAlbumList = $user->userAlbumList;
        
            // Tællere
            $created = 0;
            $deleted = 0;
       
    
            // Databasealbums løbes igennem. Hvis de ikke findes i spotify-arrayet, slettes de i databasen.
            foreach($userAlbumList as $userAlbum) {
                $index = array_search($userAlbum->album, $spotifyAlbumList);
                if ($index === FALSE){
                    $deleted++;
                    // Slettes brugeralbum i db
                    $this->deleteUserAlbum($userAlbum->userAlbumId); 
                }
            }
            
            // Spotifyalbums løbes igennem. Hvis de ikke findes i databasen, oprettes de i databasen.
            $dbalbums = $user->getAlbumList();
            foreach($spotifyAlbumList as $spotifyAlbum) {
                $index = array_search($spotifyAlbum, $dbalbums );
                if ($index === FALSE){
                    $created++;
                    // oprette spotifyalbum i db
                    $this->createUserAlbum($userId, $spotifyAlbum); 
                }  
            
            }
            
            //return "Databasen er opdateret: $deleted albums blev slettet, $created albums blev oprettet.";
            return TRUE;       
        } else {
            // Brugeren findes ikke
            return FALSE;
        }
    }
    
    
    /* ----------------------------- private hjælpe-metoder --------------------------------- */
    
    /**
     * Oprettet et brugeralbum i databasen
     *
     * Metoden (der er private) bruges kun internt i klassen, derfor bruges ikke prepared statements.
     * Vi kan bruge et album-objekt som parameter, da primærnøglen for albums ikke skabes i databasen.
     *
     * @param $userId   et Spotify-bruger-id
     * @param $album    et album-objekt
     * @return          et brugeralbum (enten nyt eller gammmelt)
     */
    private function createUserAlbum(string $userId, Album $album) : UserAlbum {
        
        // Vi sikrer os at albummet findes
        $album = $this->createAlbum($album);
        
        $query = "SELECT * FROM user_albums WHERE user_id='$userId' AND album_id='$album->albumId'";
        $result = $this->conn->query($query);
        if(!$result) die("Fejl: Kunne ikke hente brugeralbuminfo fra databasen.");
        
        if (!$result->num_rows) {
            // Brugeralbummet findes ikke
            
            $query = "INSERT INTO user_albums VALUES (NULL, '$userId', '$album->albumId')";
            $result = $this->conn->query($query);
            if(!$result) die("Fejl: Kunne ikke indsætte brugeralbum i databasen.");
            $userAlbumId = $this->conn->insert_id;
            
            $userAlbum = new UserAlbum($userAlbumId, $album);
            
            return $userAlbum;
  
        } else {
            // Brugeralbummet findes i forvejen
            $row = $result->fetch_assoc();
            $userAlbum = new UserAlbum($row["user_album_id"], $album);      
        
            return $userAlbum;
        }
       
    }
      
    /**
     * Fjerner et brugeralbum i databasen
     *
     * Taglisten slettes, men tags og albums slettes ikke.
     * Metoden (der private) bruges kun internt i klassen, derfor bruges ikke prepared statements.
     *
     * @param $userAlbumId  et brugeralbum-id
     * @return              TRUE hvis en række i user_albums blev slettet, eller FALSE
     */
    private function deleteUserAlbum(int $userAlbumId) : bool  {
        
        $query = "DELETE FROM user_albums WHERE user_album_id='$userAlbumId'";
        $result = $this->conn->query($query);   
        if(!$result) die("Fejl: kunne ikke slette brugeralbum i databasen.");
        
        if(!$this->conn->affected_rows) {
            // Der blev ikke slettet en indførsel
            return FALSE;
        } else {
            // Der blev slettet en indførsel. Slet tags.
            $query = "DELETE FROM tag_mappings WHERE user_album_id='$userAlbumId'";
            $result = $this->conn->query($query);   
            if(!$result) die("Fejl: kunne ikke slette tags fra brugeralbummet i databasen.");         
            
            return TRUE;
        }
        
    }
    
}

?>