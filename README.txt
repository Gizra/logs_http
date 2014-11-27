INTRODUCTION
------------
Loggly HTTP Provides JSON event pushing to Loggly via the tag/http endpoint. 
These events can be searched and used for alerts in Loggly by "tag:http"

The tag/http method that this module provides is very useful when the 
Loggly syslog agent is not an option such as when a web hosting limitation 
restricts installing custom web server software. This module provides a 
decoupled push via watchdog depending on severity levels.

REQUIREMENTS
------------
This module requires the following :
* An active Loggly account and the Loggly account API Token

* Composer
  https://getcomposer.org/doc/00-intro.md#using-composer

INSTALLATION
------------
* From within the loggly_http folder, run composer install

* After you run composer install, continue to install the loggly_http module 
  as you would normally install a contributed Drupal module.
  See: https://drupal.org/documentation/install/modules-themes/modules-7
  for further information.

CONFIGURATION
-------------
* Configure user permissions in Administration » People » Permissions:
  - Administer Loggly HTTP client

* Add your Loggly API Token:
  - Administration » Configure » Services » Loggly HTTP Client
  - admin/config/services/loggly-http-client
