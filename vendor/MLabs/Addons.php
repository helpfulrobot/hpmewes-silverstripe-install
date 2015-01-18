<?php
namespace MLabs;

class Addons {
    
    // default addons which are installed with silverstripe
    // new addons should added manually in composer.json for new updates
    // this should only use for new installs of silverstripe projects
    // consider that the sort order is reversed in composer.json is the last on first
    private static $require = array(
        '       "hpmewes/silverstripe-googlemaps": "dev-master"',
        '       "hpmewes/silverstripe-facebookconnect": "dev-master",',
        '       "hpmewes/silverstripe-gallery": "1.*@dev",',
        '       "hpmewes/silverstripe-siteconfigextension": "dev-master",',
        '       "gdmedia/silverstripe-frontend-admin": "dev-master",',
        '       "unclecheese/kickassets": "dev-master",',
        '       "unclecheese/bootstrap-forms": "dev-master",',
        '       "unclecheese/zen-fields": "dev-master",',
        '       "unclecheese/display-logic": "dev-master",',
        '       "silverstripe-australia/memberprofiles": "dev-master",',
        '       "silverstripe/userforms": "*",'
    );
    
    public static function getRequire() {
        return self::$require;
    }
    
}
?>
