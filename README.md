[![Build Status](https://travis-ci.org/Gizra/logs_http.svg?branch=7.x-1.x)](https://travis-ci.org/Gizra/logs_http)

# Logs HTTP

> Provides JSON event pushing to Logs via the tag/http endpoint.

The tag/http method that this module provides is very useful when the
Logs syslog agent is not an option such as when a web hosting limitation
restricts installing custom web server software. This module provides a
decoupled push via watchdog depending on severity levels.

The module can be used with any service that accepts HTTP such as [Logstash](http://logstash.net/), or paid services such as [Loggly](loggly.com)

## Credits

* [Gizra](http://gizra.com)
* [adam (prdctvtxt)](https://www.drupal.org/u/prdctvtxt) who created the original version
