<?php
namespace MLabs;

class Addons {
    
    // default addons which are installed with silverstripe
    // new addons should added manually in composer.json for new updates
    // this should only use for new installs of silverstripe projects
    // consider that the sort order is reversed in composer.json is the last on first
    private static $requires = array(
        '       "hpmewes/silverstripe-googlemaps": "dev-master"',
        '       "hpmewes/silverstripe-googlesitemaps": "dev-master"',
        '       "hpmewes/silverstripe-facebookconnect": "dev-master",',
        '       "hpmewes/silverstripe-gallery": "1.*@dev",',
        '       "hpmewes/silverstripe-siteconfigextension": "dev-master",',
        '       "hpmewes/silverstripe-bootstrap-forms": "dev-master",',
        '       "jonom/silverstripe-focuspoint": "dev-master",',
        '       "gdmedia/silverstripe-frontend-admin": "dev-master",',
        '       "unclecheese/kickassets": "dev-master",',
        '       "unclecheese/zen-fields": "dev-master",',
        '       "unclecheese/display-logic": "dev-master",',
        '       "silverstripe-australia/memberprofiles": "dev-master",',
        '       "silverstripe/userforms": "*",'
    );
    
    private static $extensions = array(
        'BootstrapForms',
        'FacebookConnect',
        'GoogleSitemap'
    );

    /**
     * add extension for custom configs, templates or classes to a addon which should not git controlled
     * because the files where copied from installer template to silverstripe folders
     */
    public static function addExtensions() {
        foreach(self::$extensions as $extension) {
            $extensionClass = "\MLabs\\".$extension."Extension";

            $extensionClass::add();
        }
    }
    
    public static function getRequire() {
        return self::$requires;
    }
    
}
?>
