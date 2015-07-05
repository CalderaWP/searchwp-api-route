![Banner](https://calderawp.com/wp-content/uploads/2015/06/WPORG_SearchWP.png)

SearchWP API
============

Adds an endpoint to the WordPress REST API for searching via [SearchWP](https://searchwp.com/?ref=121) -- the best tool for improving the usefulness and performance of WordPress search.

This plugin is a free plugin by [CalderaWP](https://CalderaWP.com). It is not an official add-on for SearchWP and is no way associated with SearchWP or the National Football League.

* Requires WordPress REST API (WP-API) 2.0-beta2 or later.
* Requires SearchWP Version 2.6 or later

### Example Queries
For a complete list of possible queries, see: [https://calderawp.com/doc/searchwp-api-queries/](https://calderawp.com/doc/searchwp-api-queries/)
* `wp-json/swp_api/search?s=jedi&egnine=star-wars`
* `wp-json/swp_api/search?&tax_query[field]=slug&tax_query[taxonomy]=categories&tax_query[terms]=1`
* `wp-json/swp_api/search?meta_query[key]=jedi&meta_query[value]=luke&tax_query[compare]=IN`

### License & Copyright
* Copyright 2015  Josh Pollock for CalderaWP LLC.

* Licensed under the terms of the [GNU General Public License version 2](http://www.gnu.org/licenses/gpl-2.0.html) or later.

* Please share with your neighbor.

