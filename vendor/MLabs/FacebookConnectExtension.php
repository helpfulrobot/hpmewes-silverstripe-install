<?php
namespace MLabs;

class FacebookConnectExtension extends Extension {

    // register your website / app https://developers.facebook.com/apps/?action=create
    private static $facebook_app_id     = "[app_id]";       // app id of facebook app
    private static $facebook_api_secret = "[api_secret]";   // app secret of facebook app
    
    public static function getFacebookAppId() { return self::$facebook_app_id; }
    public static function getFacebookApiSecret() { return self::$facebook_api_secret; }
    
    public static function add() {
        parent::add();
        
        self::$facebook_app_id = Installer::getComposerEvent()->getIO()->ask(":: enter your facebook app id: ", "[app_id]");
        self::$facebook_api_secret = Installer::getComposerEvent()->getIO()->ask(":: enter your facebook app secret: ", "[app_secret]");
        
        File::replaceContent(Installer::getRootDirConfig().self::getConfigfile(), "[app_id]", self::getFacebookAppId());
        File::replaceContent(Installer::getRootDirConfig().self::getConfigfile(), "[api_secret]", self::getFacebookApiSecret());
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
