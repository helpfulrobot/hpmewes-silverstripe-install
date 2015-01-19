<?php
namespace MLabs;

class FacebookConnectExtension extends Extension {

    public static function add() {
        parent::add();
        
        File::replaceContent(Installer::getRootDirConfig().self::getConfigfile(), "[app_id]", Installer::getFacebookAppId());
        File::replaceContent(Installer::getRootDirConfig().self::getConfigfile(), "[api_secret]", Installer::getFacebookApiSecret());
        File::addContent(Installer::getRootDirTheme()."Includes/Footer.ss", "<% include FacebookLoginLink %>", "SilverStripe</a></small>");
        
        Installer::getComposerEvent()->getIO()->write(":: added facebookconnect extension");
    }

    protected static function getExtension() {
        return "config/facebookconnect.yml";
    }
    
    protected static function getTemplates() {
        return array(
            "Includes" => "templates/Includes/FacebookLoginLink.ss"
        );
    }
    
}
?>
