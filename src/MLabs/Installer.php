<?php
namespace MLabs;

use Composer\Script\Event;

class Installer {
    
    public static function postUpdate(Event $event) {
        self::tasks($event);
        
        echo ":: mlabs installer post update done...";
    }
    
    public static function postInstall(Event $event) {
        self::tasks($event);
        
        echo ":: mlabs installer post install done...";
    }
    
    protected static function tasks(Event $event) {
        exec("chown www-data:www-data ../../../ -R");
        echo ":: make domain readable for www";
        
        echo ":: mlabs installer tasks done...";
    }
    
}
?>
