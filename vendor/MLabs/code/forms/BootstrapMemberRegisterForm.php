<?php
class BootstrapMemberRegisterForm extends Extension {
    
    public function updateProfileForm(Form $form) {
        if(!Config::inst()->get('BootstrapForm', 'bootstrap_included')) {
            Requirements::css(BOOTSTRAP_FORMS_DIR.'/css/bootstrap.css');
        }
        if(!Config::inst()->get('BootstrapForm', 'jquery_included')) {
            Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
        }
        if(!Config::inst()->get('BootstrapForm', 'bootstrap_form_included')) {
       	Requirements::javascript(BOOTSTRAP_FORMS_DIR."/javascript/bootstrap_forms.js");
        }        
        $form->Fields()->bootstrapify();
        $form->Actions()->bootstrapify();
        $form->addExtraClass('well');
        $form->addExtraClass('form-horizontal');
        $form->setTemplate('BootstrapForm');
    } 

}
?>
