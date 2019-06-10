<?php
//declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            Tag::class,
            new Tag(1, 'Ny musik')
        );
    }


    public function testCannotBeCreatedFWithJustOneParameter(): void
    {
        $this->expectException(ArgumentCountError::class);

        new Tag(1);
    }
    
}


