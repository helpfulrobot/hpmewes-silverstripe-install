<?php
namespace MLabs;

use Composer\Script\Event;

class Installer {
    // Todo: all configs should be external in environment.yml file
    /**
     * installer settings
     */
    private static $version                 = "0.1";
    private static $version_silverstripe    = "3.1.12";
    
    private static $config_from = "file";  // cli | file | database
    
    /**
     * silverstripe filestructure
     * 
     * code/forms
     * code/extensions
     * code/dataobjects
     * code/controllers
     * templates/Forms
     */
    private static $root_dir_code       = "mysite/code/";               // root directory of mysite
    private static $root_dir_theme      = "themes/simple/templates/";   // root directory for standard theme
    
    private static $root_dir_config     = "mysite/_config/";            // root directory for config
    private static $root_dir_vendor     = "vendor/MLabs/";              // root directory for MLabs vendor
    
    /**
     * config path settings
     */
    private static $config_composer                 = "composer.json";              // config file for composer
    private static $config_silverstripe             = "mysite/_config/config.yml";  // yml config file for silverstripe
    private static $config_silverstripe_old         = "mysite/_config.php";         // php config file for silverstripe
    private static $config_silverstripe_environment = "_ss_environment.php";        // environment settings
    
    /**
     * webserver settings
     */
    private static $root_dir_web        = "/var/customers/webs/";       // root directory for web
    private static $root_dir_logfiles   = "/var/customers/logs/";       // root directory of mysite
    
    private static $owner_user_web  = "www-data";
    private static $owner_group_web = "www-data";
    
    /**
     * silverstripe project settings
     */
    private static $project_company             = "MLabs Development and Design";
    private static $project_company_adress      = "Kirchgasse 31";
    private static $project_company_destination = "92360 Mühlhausen";
    private static $project_company_email       = "info@mlaboratories.de";
    private static $project_company_web         = "http://mlaboratories.de";
    
    // environment settings
    private static $project_database_server   = "localhost";
    private static $project_database_name     = "[database_name]";
    private static $project_database_username = "[database_username]";
    private static $project_database_password = "[database_password]";
    private static $project_environment_type  = "live";
    private static $default_admin_username    = "admin";
    private static $default_admin_password    = "[default_admin_password]";
    
    private static $domain              = "[domain]";              // domain for project
    private static $froxlor_username    = "[froxlor_username]";    // client username in froxlor

    private static $event   = null;
    
    public static function postUpdate(Event $event) {
        self::$event = $event;
        
        // run tasks in update mode
        self::tasks(true);
        
        self::$event->getIO()->write(":: mlabs installer post update done...");
    }
    
    public static function postInstall(Event $event) {
        self::$event = $event;
        
        // run tasks in default install mode
        self::tasks();
        
        self::$event->getIO()->write(":: mlabs installer post install done...");
    }
    
    public static function getComposerEvent() { return self::$event; }
    
    public static function getRootDirCode() { return self::$root_dir_code; }
    public static function getRootDirTheme() { return self::$root_dir_theme; }
    public static function getRootDirConfig() { return self::$root_dir_config; }
    public static function getRootDirVendor() { return self::$root_dir_vendor; }
    
    /**
     * task handler with install or update mode
     * 
     * @param boolean $update true then some tasks have a special behaviour
     */
    protected static function tasks($update = false) {        
        // check some system requirements
        self::checkSystemRequirements();
        
        self::moveFiles($update);
        self::copyFiles($update);
        
        // not neede in update mode
        if(!$update) {
            self::changeOwner();
            self::executableUserRights();
            
            self::getConfig();
            // some config settings
            self::addEnvironmentSettings();
            
            // before all other tasks can run silverstripe must installed via browser install.php
            // in framework/dev/install.php is a command install as argument via commandline check if installer can run via cli
            // in case can be that sake dev/build is the same... in config only theme and language must be setted check plz
            if(!self::$event->getIO()->askConfirmation(":: run http://[domain]/install.php when done type [yes] here (let empty for abort): ", false)) {
                self::exitInstaller(':: mlabs installer abort');
            }
            self::createFolderStructure();
            // add some nice addons and customized changes
            self::addRepositories();
            // silverstripe cms changes
            self::addConfigErrorLog();
            self::addConfigEmailLog();
            self::requirementsGoogleJquery();
            self::blockRequirements();
            self::requirementsCDNBootstrap();
            self::requirementsJqueryChosen();
            self::addMeta();
            
            self::resetUserRights();
        }
        // check why site is not flushed and builded
        self::build();
        // rename installer.php
        File::move("install.php",  "_install.php");
        self::$event->getIO()->write(":: move silverstripe installer");
        
        self::$event->getIO()->write(":: mlabs installer tasks done run http://[domain]/dev/build?flush=all in browser builded in built has no effekt at this time...");
    }
    
    protected static function exitInstaller($message) {
        self::$event->getIO()->write("$message");
        exit();
    }

    /**
     * get config for installer
     */
    protected static function getConfig() {
        // Todo: use a class like Config with functions like Config->get('domain') which readed from environment.yml
        switch(self::$config_from) {
            case "cli":
                self::getConfigFromCommandline();
                break;
            case "file":
                self::getConfigFromFile();
                break;
            case "database":
                self::getConfigFromDatabase();
                break;
        }
    }

        /**
     * get config information from command line
     */
    protected static function getConfigFromCommandline() {
        self::$domain = self::$event->getIO()->ask(":: type the domain here without http:// or https:// (let empty for default placeholder [domain]): ", "[domain]");
        self::$froxlor_username = self::$event->getIO()->ask(":: type the client username which added to froxlor here (let empty for default placeholder [froxlor-username]): ", "[froxlor-username]");
        self::$project_database_server = self::$event->getIO()->ask(":: type the database name here (let empty for default placeholder [localhost]): ", "localhost");
        self::$project_database_name = self::$event->getIO()->ask(":: type the database name here (let empty for default placeholder [SS_mysite]): ", "SS_mysite");
        self::$project_database_username = self::$event->getIO()->ask(":: type the database user name here (let empty for default placeholder [database_username]): ", "[database_username]");
        self::$project_database_password = self::$event->getIO()->ask(":: type the database password here (let empty for default placeholder [database_password]): ", "[database_password]");
        self::$project_environment_type = self::$event->getIO()->ask(":: type the environment type here dev|test|live (let empty for default placeholder [live]): ", "live");
        self::$default_admin_username = self::$event->getIO()->ask(":: type the default admin username here (let empty for default placeholder [admin]): ", "admin");
        self::$default_admin_password = self::$event->getIO()->ask(":: type the default admin password here (let empty for default placeholder [12345]): ", "12345");
    }

    protected static function getConfigFromFile() {
        // Todo:
        self::$event->getIO()->write(":: getConfigFromFile() not implemented yet");
    }

    protected static function getConfigFromDatabase() {
        // Todo:
        self::$event->getIO()->write(":: getConfigFromDatabase() not implemented yet");
    }

    /**
     * check for some system requirements which are needed to run installer
     */
    protected static function checkSystemRequirements() {
        self::hasNginx();
        self::hasFroxlor();
        self::hasSake();
    }

    /**
     * change the owner-user and owner-group to get access via php5-fpm process
     * recursiv for project
     */
    protected static function changeOwner() {
        // change owner for project recursiv
        exec("chown ".self::$owner_user_web.":".self::$owner_group_web." ".self::$root_dir_web."  -R");
        self::$event->getIO()->write(":: make domain readable for www");      
    }

    /**
     * create a default folder structure for projects
     */
    protected static function createFolderStructure() {
        exec("mkdir ".self::$root_dir_code."forms");
        exec("mkdir ".self::$root_dir_code."extensions");
        exec("mkdir ".self::$root_dir_code."dataobjects");
        exec("mkdir ".self::$root_dir_code."controllers");
        exec("mkdir ".self::$root_dir_theme."Forms");
        self::$event->getIO()->write(":: create default folder structure for projects");
    }

    /**
     * move the silverstripe-installer from vendor to project root
     * and delete directory structure in vendor
     * 
     * @param boolean $update
     */
    protected static function moveFiles($update) {
        $root_dir_silverstripe_installer = "vendor/silverstripe/installer";
        
        // check if folder exists
        if(file_exists($root_dir_silverstripe_installer) && is_dir($root_dir_silverstripe_installer)) {
            // remove composer.json in silverstripe installer to avoid conflicts
            File::delete("$root_dir_silverstripe_installer/composer.json");
            // not move folders when update is running because overwriting existing files
            if(!$update) {
                // move folders
                File::move("$root_dir_silverstripe_installer/assets", ".");
                File::move("$root_dir_silverstripe_installer/mysite", ".");
            }
            // move files
            File::move("$root_dir_silverstripe_installer/*.*", ".");
            // espacially for .git .gitignore and so one
            File::move("$root_dir_silverstripe_installer/.*", ".");
            // remove the folder
            File::deleteFolder("vendor/silverstripe");
        }
    }
    
    /**
     * copy some files
     * 
     * @param boolean $update
     */
    protected function copyFiles($update) {
        File::copy(self::getRootDirVendor().'css/*.css', self::getRootDirTheme().'css');
        File::copy(self::getRootDirVendor().'js/*.js', self::getRootDirTheme().'js');
        File::copy(self::getRootDirVendor().'images/*.*', self::getRootDirTheme().'images');
        
        self::$event->getIO()->write(":: copied some needed files");
    }

    /**
     * build and flush silverstripe cms
     */
    protected static function build() {
        // build database
        passthru('sake dev/build "flush=all"');
        self::$event->getIO()->write(":: build database and flush cache");
    }
    
    /**
     * add silverstripe addons must done here an error occurs if it in composer.json on install it
     */
    protected static function addRepositories() {
        foreach(Addons::getRequire() as $require) {
            File::addContent(self::$config_composer, $require, 'silverstripe/installer');
        }
        File::replaceContent(self::$config_composer, '"silverstripe/installer": "3.1.11"', '"silverstripe/installer": "3.1.11",');
        
        // run composer again which triggered Installer.php again
        passthru("composer update");
        
        // add some customized extensions to addons
        Addons::addExtensions(self::$event);
        
        self::changeOwner();
    }

    /**
     * add some settings to __ss_environment.php for silverstripe needed to run sake
     */
    protected static function addEnvironmentSettings() {
        File::replaceContent(self::$config_silverstripe_environment, '[database_server]', self::$project_database_server);
        File::replaceContent(self::$config_silverstripe_environment, '[database_name]', self::$project_database_name);
        File::replaceContent(self::$config_silverstripe_environment, '[database_username]', self::$project_database_username);
        File::replaceContent(self::$config_silverstripe_environment, '[database_password]', self::$project_database_password);
        File::replaceContent(self::$config_silverstripe_environment, '[environment_type]', self::$project_environment_type);
        File::replaceContent(self::$config_silverstripe_environment, '[default_admin_username]', self::$default_admin_username);
        File::replaceContent(self::$config_silverstripe_environment, '[default_admin_password]', self::$default_admin_password);
        File::replaceContent(self::$config_silverstripe_environment, '[domain]', self::$domain);
        File::replaceContent(self::$config_silverstripe_environment, '[root_dir_web]', '"'.self::$root_dir_web.self::$froxlor_username.'/'.self::$domain.'"');
    }

    /**
     * adding warn and error log to silverstripe _config.php
     */
    protected static function addConfigErrorLog() {
        self::$event->getIO()->write(":: add logging to Silverstripe _config.php");
        File::addContent(
            self::$config_silverstripe_old, 
            "SS_Log::add_writer(new SS_LogFileWriter('".self::$root_dir_logfiles.self::$froxlor_username."-ss.log'), SS_Log::WARN, '<=');"
        );
    }
    
    /**
     * adding warn and error log for email
     */
    protected static function addConfigEmailLog() {
        self::$event->getIO()->write(":: add email logging to Silverstripe _config.php");
        File::addContent(
            self::$config_silverstripe_old, 
            "SS_Log::add_writer(new SS_LogEmailWriter('admin@".self::$domain."'), SS_Log::WARN, '<=');"
        );
    }
    
    /**
     * add Requirements::block for delivered jquery to use newer version in frontend
     */
    protected static function blockRequirements() {
        File::addContent(
            self::$root_dir_code.'Page.php', 
            "                Requirements::block(THIRDPARTY_DIR . '/jquery/jquery.js');", 
            "http://doc.silverstripe.org/framework/en/reference/requirements"
        );
        File::addContent(
            self::$root_dir_code.'Page.php', 
            "                Requirements::block(FRAMEWORK_DIR . '/admin/thirdparty/chosen/chosen/chosen.jquery.js');", 
            "/jquery/jquery.js"
        );
    }

    /**
     * add Requirements::javascript jquery from googleapis in _config.php that is loaded bevore all other javascripts can be loaded
     * prevent $. is not defined
     */
    protected static function requirementsGoogleJquery() {
        $version = self::$event->getIO()->ask(":: type jquery version here (let empty for default 2.1.3): ", "2.1.3");
        
        File::addContent(
            self::$config_silverstripe_old, 
            "Requirements::javascript('http://ajax.googleapis.com/ajax/libs/jquery/$version/jquery.min.js');"
        );
    }
    
    protected static function requirementsJqueryChosen() {
        File::addContent(
            self::$root_dir_code.'Page.php', 
            "                Requirements::javascript(THEMES_DIR.'/'. SSViewer::current_theme().'/js/jquery.chosen.min.js');", 
            'bootstrap.min.js'
        );
        File::addContent(
            self::$root_dir_code.'Page.php', 
            "                Requirements::themedCSS('jquery.chosen.min');", 
            'bootstrap-theme.min.css'
        );
    }


    /**
     * add Requirements:: for bootstrap from cdn
     */
    protected static function requirementsCDNBootstrap() {
        $version = self::$event->getIO()->ask(":: type bootstrap version here (let empty for default 3.3.2): ", "3.3.2");
        
        File::addContent(
            self::$root_dir_code.'Page.php', 
            "                Requirements::javascript('https://maxcdn.bootstrapcdn.com/bootstrap/$version/js/bootstrap.min.js');", 
            'Requirements::block'
        );
        File::addContent(
            self::$root_dir_code.'Page.php', 
            "                Requirements::css('https://maxcdn.bootstrapcdn.com/bootstrap/$version/css/bootstrap-theme.min.css');", 
            'Requirements::block'
        );
        File::addContent(
            self::$root_dir_code.'Page.php', 
            "                Requirements::css('https://maxcdn.bootstrapcdn.com/bootstrap/$version/css/bootstrap.min.css');", 
            'Requirements::block'
        );
    }

    /**
     * add meta name, content and $MetaTags(true) to default theme
     */
    protected static function addMeta() {
        // Todo: compan_* variables from command line otherwise use this one
        File::addContent(
            self::$root_dir_theme.'Page.ss', 
            '       <meta name="author" content="'.self::$project_company.', '.self::$project_company_adress.', '.self::$project_company_destination.', '.self::$project_company_web.', '.self::$project_company_email.'">', 
            '<meta http-equiv'
        );
        File::replaceContent(
            self::$root_dir_theme.'Page.ss',
            '$MetaTags(false)',
            '$MetaTags(true)'
        );
    }

    /**
     * make some files and folders executable via web to add some configs or upload assets
     */
    protected static function executableUserRights() {
        // change user rights to grant access from web
        exec("chmod 755 assets/ -R");
        self::$event->getIO()->write(":: make assets executable for www to upload files via Silverstripe");
        exec("chmod 775 ".self::$config_silverstripe_old);
        self::$event->getIO()->write(":: make _config.php executable for www to add some lines for installer");
        exec("chmod 775 ".self::$config_silverstripe);
        self::$event->getIO()->write(":: make config.yml executable for www to add some lines for installer");
    }

    /**
     * reset user rights to default to prevent access via web
     */
    protected static function resetUserRights() {
        // reset rights 644 for files
        exec("chmod 644 ".self::$config_silverstripe_old);
        self::$event->getIO()->write(":: reset rights for _config.php");
        exec("chmod 644 ".self::$config_silverstripe);
        self::$event->getIO()->write(":: reset rigths config.yml");
    }
    
    /**
     * check if nginx is installed
     * 
     * @return boolean
     */
    protected static function hasNginx() {
        if(strpos(exec("dpkg --get-selections | grep -E 'nginx[^\-]'"), 'nginx') !== false) return true;
        
        self::exitInstaller(':: mlabs installer abort because nginx is not installed and nginx is only supported at moment');
    }
    
    /**
     * check if froxlor installed
     * 
     * @return boolean
     */
    protected static function hasFroxlor() {
        if(strpos(exec("dpkg --get-selections | grep -E 'froxlor'"), 'froxlor') !== false) return true;
        
        self::exitInstaller(':: mlabs installer abort because froxlor is not installed and froxlor is only supported at moment');
    }
    
    /**
     * check if sake installed otherwise install it
     * 
     * @return boolean
     */
    protected static function hasSake() {
        if(file_exists('/usr/bin/sake')) return true;
        
        exec('./framework/sake installsake');
        self::$event->getIO()->write(":: silverstripe sake was successfully installed");
    }
    
}
?>
