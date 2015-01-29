# Logs HTTP

Logs HTTP Provides JSON event pushing to Logs via the tag/http endpoint.
These events can be searched and used for alerts in Logs by "tag:http"

The tag/http method that this module provides is very useful when the
Logs syslog agent is not an option such as when a web hosting limitation
restricts installing custom web server software. This module provides a
decoupled push via watchdog depending on severity levels.

