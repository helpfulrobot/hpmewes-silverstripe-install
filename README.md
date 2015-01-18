# SilverStripe Installer
Automated SilverStripe installer with cdn Bootstrap and googleapis jQuery.
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
* Facebook Connect (https://github.com/hpmewes/silverstripe-facebookconnect)
* Gallery (https://github.com/frankmullenger/silverstripe-gallery)
* Extended SiteConfig (https://github.com/hpmewes/silverstripe-siteconfigextension)
* Frontend Admin (https://github.com/guru-digital/frontend-admin)
* Kickassets (https://github.com/unclecheese/KickAssets)
* Bootstrap Forms (https://github.com/unclecheese/silverstripe-bootstrap-forms)
* Zen Fields (https://github.com/unclecheese/silverstripe-zen-fields)
* Display Logic (https://github.com/unclecheese/silverstripe-display-logic)
* Memberprofiles (https://github.com/silverstripe-australia/silverstripe-memberprofiles)
* Userforms (https://github.com/silverstripe/silverstripe-userforms)

# Dev
Some things to do and planned in future.
If someone will help you're welcome @meweshp@googlemail.com.

## Todo

* search in project for "Todo:"
* see future features, modules and so on

## Future Features (SilverStripe)

* extend config.yml
* extend extensions.yml
* check for php5-fpm
* write nginx config
* ssl settings to launch admin area over https://
* write php5-fpm config (change memory_limit to 64M)
* default contact page with userform and google map

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
* focuspoint
* spamprotection
* edit lock
* timed notices
* google sitemaps
* photo gallery

and some more...

## Future Features (Froxlor)
* get [froxlor-username] for installer
* get [domain] for installer
* silverstripe one click installation

## Known issues
* "You are *required* to use the date.timezone setting or the date_default_timezone_set() function" when sake running with dev/build "flush=1°
* $.browser is undefined in scrip.js on simple theme from silverstripe use modernizr
