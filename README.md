# SilverStripe Installer
Automated SilverStripe installer with cdn Bootstrap and googleapis jQuery.
Updated version of jQuery chosen is also inside.
You should have root rights when you run this script.

Bevore fork ask @meweshp@googlemail.com
Some features will be moved in other module after development. it's in still early alpha.

## How to get started

### Create new project
composer create-project hpmewes/silverstripe-install -s dev ROOT_SITE_DIR

### Install manually
Copy files into your ROOT_SITE_DIR and run "composer install"

## Webserver Support

* Nginx

## Server Management Panel Support

* Froxlor (comming soon)

## What is inside
All included Packages are preconfigured. You should only have a look over there.

### SilverStripe core components

* SilverStripe Framework (https://github.com/silverstripe/silverstripe-framework)
* Silverstripe CMS (https://github.com/silverstripe/silverstripe-cms)

### SilverStripe modules

* Googlemaps (https://github.com/hpmewes/silverstripe-googlemaps)
* GoogleSitemap (https://github.com/silverstripe-labs/silverstripe-googlesitemaps)
* Facebook Connect (https://github.com/hpmewes/silverstripe-facebookconnect)
* Gallery (https://github.com/frankmullenger/silverstripe-gallery)
* Extended SiteConfig (https://github.com/hpmewes/silverstripe-siteconfigextension)
* Focuspoint (https://github.com/jonom/silverstripe-focuspoint)
* Frontend Admin (https://github.com/guru-digital/frontend-admin)
* Kickassets (https://github.com/unclecheese/KickAssets)
* Bootstrap Forms (https://github.com/unclecheese/silverstripe-bootstrap-forms)
* Zen Fields (https://github.com/unclecheese/silverstripe-zen-fields)
* Display Logic (https://github.com/unclecheese/silverstripe-display-logic)
* Memberprofiles (https://github.com/silverstripe-australia/silverstripe-memberprofiles)
* Userforms (https://github.com/silverstripe/silverstripe-userforms)

### Customized Extensions
Use costumized Extensions without control of git.
Some addons must be modified to get started. Now it's away. Once time you've get it run you can copy it to installer
and in next install this would be done automated for you.
For Example look in vendor\MLabs\Extension, vendor\MLabs\BootstrapFormExtension, vendor\MLabs\FacebookConnectExtension

### File handling
See more in File under vendor\MLabs\File

* copy
* delete
* delete folder
* move
* add content
* replace content

# Dev
Some things to do and planned in future.
If someone will help you're welcome @meweshp@googlemail.com.

## Todo

* search in project for "Todo:"
* create a new template with all the stuff in it from config, cli or whatelse
* see future features, modules and so on

## Future Features (SilverStripe)

* extend config.yml
* extend extensions.yml

* default contact page added to cms with userform and google map
* default member profile page added to cms with email from, relationship between registered user and group
* default gallery template

## Future Modules (SilverStripe)

* news
* translation (discuss which should included)
* silversmith (optional from installer)

## Future Feature Admin Packages (SilverStripe)
Packages should be selected and installed over SilverStripe Admin (only for admin).

* dropzone
* dynamictemplate
* bootstrap autocomplete
* bootstrap tag field
* spamprotection
* edit lock
* timed notices
* photo gallery
* google analytics

and some more...

## Future Features (Froxlor)
* get [froxlor-username] for installer
* get [domain] for installer
* silverstripe one click installation
* check for php5-fpm
* write nginx config
* ssl settings to launch admin area over https://
* write php5-fpm config (change memory_limit to 64M)

## Known issues
* BootstrapFormsExtension - not all forms are copied only BootstrapMemberProfileRegisterFrom
* sake build/dev "flush=all" - has no effect must checked
* "You are *required* to use the date.timezone setting or the date_default_timezone_set() function" - when sake running with dev/build "flush=1°
* $.browser is undefined in scrip.js on simple theme from silverstripe - use modernizr
* after installing via browser redirecting to site not found index.php/home/successfullyinstalled?flush=1&flushtoken=2f28be2e403429fbcb40b9dd3aa43d7f - i think a rule in nginx is needed
