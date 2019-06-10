<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    public function testTagCanBeCreated(): void
    {
        $db = new Database();
        
        $tag = $db->createTag("Symfonisk musik");
        
        $this->assertInstanceOf(
            Tag::class,
            $tag
        );
        $this->assertSame(14, $tag->tagId);
        $this->assertSame('Symfonisk musik', $tag->tagName);
    }
    
    public function testAlbumCanBeCreated(): void
    {
        $db = new Database;
        
        $testAlbum = new Album('0nPh4WUdoJMkRpYUF2LXaB', 'https://i.scdn.co/image/ab1d7e05ea256e9612114238b39783ab7b21d466', 'Bouncing With Bud', 'Bud Powell Trio');

        $album = $db->createAlbum($testAlbum);
        
        $this->assertInstanceOf(
            Album::class,
            $album
        );
        $this->assertEquals($testAlbum, $album);
    }
    
    public function testTagCanBeAdded(): void {
        
        $db = new Database;
        
        $testTagName = "Techno";
        
        $testUserAlbumId = 159;
        
        $returnTag = $db->addUserAlbumTag($testTagName, $testUserAlbumId);
        
        $this->assertInstanceOf(
            Tag::class,
            $returnTag
        );
    }
    
    public function testUserCanBeCreated(): void {
        
        $db = new Database;
        
        $testUser = new User("test", "test", "test");
        
        $this->assertNull($db->getUser("test"));
        
        $this->assertTrue($db->createUser("test", "test", "test"));
        
        $this->assertFalse($db->createUser("test", "test", "test"));
        
    }
}
