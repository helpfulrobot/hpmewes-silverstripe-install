<?php
namespace MLabs;

class BootstrapFormsExtension extends Extension {
    
    public static function add() {
        parent::add();
        
        Installer::getComposerEvent()->getIO()->write(":: added bootstrap-forms extension");
    }
    
    protected static function getExtension() {
        return "config/bootstrapforms.yml";
    }
    
    protected static function getClasses() {
        return array(
            "forms" => "code/forms/BootstrapMemberProfileForm.php",
            "forms" => "code/forms/BootstrapMemberRegisterForm.php"
        );
    }
    
}
?>
