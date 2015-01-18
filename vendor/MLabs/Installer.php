<?php
namespace MLabs;

use Composer\Script\Event;

class Installer {
    // Todo: all configs should be external in config file
    private static $version                 = "0.1";
    private static $version_silverstripe    = "3.1.8";
    
    private static $config_from = "cli";  // cli | file
    
    private static $project_company     = "MLabs Development and Design";
    private static $project_adress      = "Kirchgasse 31";
    private static $project_destination = "92360 Mühlhausen";
    private static $project_email       = "info@mlaboratories.de";
    private static $project_web         = "http://mlaboratories.de";
    
    private static $root_dir_web        = "/var/customers/webs/";       // root directory for web
    private static $root_dir_theme      = "themes/simple/templates/";   // root directory for standard theme
    private static $root_dir_mysite     = "mysite/code/";               // root directory of mysite
    private static $root_dir_logfiles   = "/var/customers/logs/";       // root directory of mysite

    private static $composer_config_json        = "composer.json";              // config file for composer
    private static $silverstripe_config_php     = "mysite/_config.php";         // php config file for silverstripe
    private static $silverstripe_config_yml     = "mysite/_config/config.yml";  // yml config file for silverstripe
    private static $silverstripe_environment    = "_ss_environment.php";         // environment settings
    
    private static $owner_user_web  = "www-data";
    private static $owner_group_web = "www-data";
    
    // environment settings
    private static $database_server         = "localhost";
    private static $database_name           = "[database_name]";
    private static $database_username       = "[database_username]";
    private static $database_password       = "[database_password]";
    private static $environment_type        = "live";
    private static $default_admin_username  = "admin";
    private static $default_admin_password  = "[default_admin_password]";
    
    // these values would be asked from command line
    private static $domain              = "[domain]";              // domain for project
    private static $froxlor_username    = "[froxlor_username]";    // client username in froxlor

    private static $event   = null;
    private static $status  = null;
    
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
    
    /**
     * task handler with install or update mode
     * 
     * @param boolean $update true then some tasks have a special behaviour
     */
    protected static function tasks($update = false) {        
        // check some system requirements
        self::checkSystemRequirements();
        
        self::moveFiles($update);
        self::changeOwner();
        
        // not neede in update mode
        if(!$update) {
            self::executableUserRights();
            
            // get config
            if(self::$config_from == "cli") self::getConfigFromCommandline();
            else self::getConfigFromFile();
            // some config settings
            self::addEnvironmentSettings();
            
            // before all other tasks can run silverstripe must installed via browser install.php
            // in framework/dev/install.php is a command install as argument via commandline check if installer can run via cli
            // in case can be that sake dev/build is the same... in config only theme and language must be setted check plz
            if(!self::$event->getIO()->askConfirmation(":: run http://[domain]/install.php when done type [yes] here (let empty for abort): ", false)) {
                self::exitInstaller(':: mlabs installer abort');
            }
            self::addRepositories();
            self::addConfigErrorLog();
            self::addConfigEmailLog();
            self::requirementsGoogleJquery();
            self::blockRequirements();
            self::requirementsCDNBootstrap();
            self::addMeta();
            
            self::resetUserRights();
        }
        // check why site is not flushed and builded
        self::build();
        // rename installer.php
        exec("mv install.php _install.php");
        self::$event->getIO()->write(":: move silverstripe installer");
        
        self::$event->getIO()->write(":: mlabs installer tasks done run http://[domain]/dev/build?flush=all in browser builded in built has no effekt at this time...");
    }
    
    protected static function exitInstaller($message) {
        self::$event->getIO()->write("$message");
        exit();
    }

    /**
     * get config information from command line
     */
    protected static function getConfigFromCommandline() {
        self::$domain = self::$event->getIO()->ask(":: type the domain here without http:// or https:// (let empty for default placeholder [domain]): ", "[domain]");
        self::$froxlor_username = self::$event->getIO()->ask(":: type the client username which added to froxlor here (let empty for default placeholder [froxlor-username]): ", "[froxlor-username]");
        self::$database_server = self::$event->getIO()->ask(":: type the database name here (let empty for default placeholder [localhost]): ", "localhost");
        self::$database_name = self::$event->getIO()->ask(":: type the database name here (let empty for default placeholder [SS_mysite]): ", "SS_mysite");
        self::$database_username = self::$event->getIO()->ask(":: type the database user name here (let empty for default placeholder [database_username]): ", "[database_username]");
        self::$database_password = self::$event->getIO()->ask(":: type the database password here (let empty for default placeholder [database_password]): ", "[database_password]");
        self::$environment_type = self::$event->getIO()->ask(":: type the environment type here dev|test|live (let empty for default placeholder [live]): ", "live");
        self::$default_admin_username = self::$event->getIO()->ask(":: type the default admin username here (let empty for default placeholder [admin]): ", "admin");
        self::$default_admin_password = self::$event->getIO()->ask(":: type the default admin password here (let empty for default placeholder [12345]): ", "12345");
    }

    protected static function getConfigFromFile() {
        // Todo:
        self::$event->getIO()->write(":: getConfigFromFile() not implemented yet");
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
     * move the silverstripe-installer from vendor to project root
     * and delete directory structure in vendor
     */
    protected static function moveFiles($update) {
        $root_dir_silverstripe_installer = "vendor/silverstripe/installer";
        
        // check if folder exists
        if(file_exists($root_dir_silverstripe_installer) && is_dir($root_dir_silverstripe_installer)) {
            // remove composer.json in silverstripe installer to avoid conflicts
            exec("rm $root_dir_silverstripe_installer/composer.json");
            // not move folders when update is running because overwriting existing files
            if(!$update) {
                // move folders
                exec("mv $root_dir_silverstripe_installer/assets .");
                exec("mv $root_dir_silverstripe_installer/mysite .");
            }
            // move files
            exec("mv $root_dir_silverstripe_installer/*.* .");
            // espacially for .git .gitignore and so one
            exec("mv $root_dir_silverstripe_installer/.* .");
            // remove the folder
            exec("rm $root_dir_silverstripe_installer/ -R");
        }
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
            self::fileAddContent(self::$composer_config_json, $require, 'silverstripe/installer');
        }
        self::fileReplaceContent(self::$composer_config_json, '"silverstripe/installer": "3.1.8"', '"silverstripe/installer": "3.1.8",');
        
        // run composer again which triggered Installer.php again
        passthru("composer update");
    }

    /**
     * add some settings to __ss_environment.php for silverstripe needed to run sake
     */
    protected static function addEnvironmentSettings() {
        self::fileReplaceContent(self::$silverstripe_environment, '[database_server]', self::$database_server);
        self::fileReplaceContent(self::$silverstripe_environment, '[database_name]', self::$database_name);
        self::fileReplaceContent(self::$silverstripe_environment, '[database_username]', self::$database_username);
        self::fileReplaceContent(self::$silverstripe_environment, '[database_password]', self::$database_password);
        self::fileReplaceContent(self::$silverstripe_environment, '[environment_type]', self::$environment_type);
        self::fileReplaceContent(self::$silverstripe_environment, '[default_admin_username]', self::$default_admin_username);
        self::fileReplaceContent(self::$silverstripe_environment, '[default_admin_password]', self::$default_admin_password);
        self::fileReplaceContent(self::$silverstripe_environment, '[domain]', self::$domain);
        self::fileReplaceContent(self::$silverstripe_environment, '[root_dir_web]', '"'.self::$root_dir_web.self::$froxlor_username.'/'.self::$domain.'"');
    }

    /**
     * adding warn and error log to silverstripe _config.php
     */
    protected static function addConfigErrorLog() {
        self::$event->getIO()->write(":: add logging to Silverstripe _config.php");
        self::fileAddContent(
            self::$silverstripe_config_php, 
            "SS_Log::add_writer(new SS_LogFileWriter('".self::$root_dir_logfiles.self::$froxlor_username."-ss.log'), SS_Log::WARN, '<=');"
        );
    }
    
    /**
     * adding warn and error log for email
     */
    protected static function addConfigEmailLog() {
        self::$event->getIO()->write(":: add email logging to Silverstripe _config.php");
        self::fileAddContent(
            self::$silverstripe_config_php, 
            "SS_Log::add_writer(new SS_LogEmailWriter('admin@".self::$domain."'), SS_Log::WARN, '<=');"
        );
    }
    
    /**
     * add Requirements::block for delivered jquery to use newer version in frontend
     */
    protected static function blockRequirements() {
        self::fileAddContent(
            self::$root_dir_mysite.'Page.php', 
            "                Requirements::block(THIRDPARTY_DIR . '/jquery/jquery.js');", 
            "// See:"
        );
    }

    /**
     * add Requirements::javascript jquery from googleapis in _config.php that is loaded bevore all other javascripts can be loaded
     * prevent $. is not defined
     */
    protected static function requirementsGoogleJquery() {
        $version = self::$event->getIO()->ask(":: type jquery version here (let empty for default 2.1.1): ", "2.1.1");
        
        self::fileAddContent(
            self::$silverstripe_config_php, 
            "Requirements::javascript('http://ajax.googleapis.com/ajax/libs/jquery/$version/jquery.min.js');"
        );
    }
    
    /**
     * add Requirements:: for bootstrap from cdn
     */
    protected static function requirementsCDNBootstrap() {
        $version = self::$event->getIO()->ask(":: type bootstrap version here (let empty for default 3.3.1): ", "3.3.1");
        
        self::fileAddContent(
            self::$root_dir_mysite.'Page.php', 
            "                Requirements::javascript('https://maxcdn.bootstrapcdn.com/bootstrap/$version/js/bootstrap.min.js');", 
            'Requirements::block'
        );
        self::fileAddContent(
            self::$root_dir_mysite.'Page.php', 
            "                Requirements::css('https://maxcdn.bootstrapcdn.com/bootstrap/$version/css/bootstrap-theme.min.css');", 
            'Requirements::block'
        );
        self::fileAddContent(
            self::$root_dir_mysite.'Page.php', 
            "                Requirements::css('https://maxcdn.bootstrapcdn.com/bootstrap/$version/css/bootstrap.min.css');", 
            'Requirements::block'
        );
    }

    /**
     * add meta name, content and $MetaTags(true) to default theme
     */
    protected static function addMeta() {
        // Todo: compan_* variables from command line otherwise use this one
        self::fileAddContent(
            self::$root_dir_theme.'Page.ss', 
            '       <meta name="author" content="'.self::$project_company.', '.self::$project_adress.', '.self::$project_destination.', '.self::$project_web.', '.self::$project_email.'">', 
            '<meta http-equiv'
        );
        self::fileReplaceContent(
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
        exec("chmod 775 ".self::$silverstripe_config_php);
        self::$event->getIO()->write(":: make _config.php executable for www to add some lines for installer");
        exec("chmod 775 ".self::$silverstripe_config_yml);
        self::$event->getIO()->write(":: make config.yml executable for www to add some lines for installer");
    }

    /**
     * reset user rights to default to prevent access via web
     */
    protected static function resetUserRights() {
        // reset rights 644 for files
        exec("chmod 644 ".self::$silverstripe_config_php);
        self::$event->getIO()->write(":: reset rights for _config.php");
        exec("chmod 644 ".self::$silverstripe_config_yml);
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

    /**
     * add some content to file
     * 
     * @param string $filename
     * @param string $content
     * @param string $after is string given $content is added in a new line after $after string in file
     */
    private static function fileAddContent($filename, $content, $after = null) {
        if(!is_null($after)) self::fileEditContent($filename, $content, $after);
        else {
            // check if file exists when add error logger
            if(file_exists($filename)) {
                // append content to file
                file_put_contents($filename, $content.PHP_EOL, FILE_APPEND);

                self::$event->getIO()->write(":: added $content to $filename");
            }
            else {
                self::$event->getIO()->write(":: failure adding $content to $filename not exists");
            }
        }
    }
    
    /**
     * replace content with other content
     * 
     * @param string $filename
     * @param string $search
     * @param string $replace
     */
    private static function fileReplaceContent($filename, $search, $replace) {
        self::fileEditContent($filename, $replace, $search, true);
    }
    
    /**
     * file editing
     * 
     * @param string $filename
     * @param string $content
     * @param string $search
     * @param boolean $replace true if the given content should replace the $search string
     */
    private static function fileEditContent($filename, $content, $search, $replace = false) {
        $lines      = array();
        $linesTmp   = array();
        $changed    = false;    // flag to set true when $search found in content
        
        // open file to read
        if(($fileHandle = fopen($filename, "r")) !== false) {
            // buffer every line
            while(($buffer = fgets($fileHandle)) !== false) {
                $lines[] = $buffer;
            }
            // close file
            fclose($fileHandle);
        }
        else {
            self::$event->getIO()->write(":: failure reading $filename perhaps not exists");
        }
        
        foreach($lines as $line) {
            // if searched string found
            if(strpos($line, $search) !== false) {
                // when search string should be replaced
                if($replace) {
                    $linesTmp[] = str_replace($search, $content, $line);
                    self::$event->getIO()->write(":: replaced $search with $content in $filename");
                }
                else {
                    $linesTmp[] = $line;
                    $linesTmp[] = $content.PHP_EOL;
                    self::$event->getIO()->write(":: added $content after $search to $filename");
                }
                
                $changed = true;
            } else {
                $linesTmp[] = $line;
            }
        }
        
        // has file changed
        if($changed) {
            // overwrite file with new content
            file_put_contents($filename, implode('', $linesTmp));
        }
        else {
            self::$event->getIO()->write(":: not added $content to $filename because $search not found");
        }
    }
    
}
?>
