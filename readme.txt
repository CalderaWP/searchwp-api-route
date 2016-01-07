=== SearchWP API ===
Contributors: Shelob9
Tags: search, rest-api, wp-api, json, searchwp, calderawp
Donate link: https://CalderaWP.com
Requires at least: 4.3.1
Tested up to: 4.4
Stable tag: 1.1.0
License: GPL version 2 or later

Run advanced searches via the WordPress REST API and SearchWP.

== Description ==
Run advanced searches via the WordPress REST API and SearchWP.

Adds an endpoint to the WordPress REST API for searching via [SearchWP](https://searchwp.com/) -- the best tool for improving the usefulness and performance of WordPress search.

This plugin is a free plugin by [CalderaWP](https://CalderaWP.com). It is not an official add-on for SearchWP.

* Requires WordPress REST API (WP-API) 2.0-beta9 or later or WordPress 4.4 or later.
* Requires SearchWP Version 2.6 or later

Technically will work without SearchWP, but queries will run through WP_Query.

=== Example Queries ===
For a complete list of possible queries, see: [https://calderawp.com/doc/searchwp-api-queries/](https://calderawp.com/doc/searchwp-api-queries/)
* `wp-json/swp_api/search?s=jedi&egnine=star-wars`
* `wp-json/swp_api/search?&tax_query[field]=slug&tax_query[taxonomy]=categories&tax_query[terms]=1`
* `wp-json/swp_api/search?meta_query[key]=jedi&meta_query[value]=luke&tax_query[compare]=IN`

== Installation ==
* Install SearchWP, and the REST API plugin (you will need to get the \"develop\" branch from Github.)
* Install this plugin.
* Activate this plugin.
* GET some queries.
* Breath in.
* Breath out.

== Frequently Asked Questions ==
=== Does It Work With Version 1 of The REST API? ===
No, it does not.

=== I Installed It And Nothing Happened ===
You are probably using version 1 of the REST API, or have not updated SearchWP past 2.6.

=== How Shiny Is This Plugin? ===
Very shiny.

== Screenshots ==


== Changelog ==
=== Version 1.1.0 - January, 2015 ===
* Complex meta queries
* Fallback to WP_Query if not possible to use SearchWP

=== Version 1.0.0 - July 6, 2015 ===
Initial release

== Upgrade Notice ==
Nothing To See Here
