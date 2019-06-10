<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'album' => '/Album.php',
                'database' => '/Database.php',
                'itagifydataaccess' => '/iTagifyDataAccess.php',
                'spotifyconnector' => '/SpotifyConnector.php',
                'tag' => '/Tag.php',
                'user' => '/User.php',
                'useralbum' => '/UserAlbum.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd