SearchWP API
============

Adds an endpoint to the WordPress REST API for searching via SearchWP.

* Requires WordPress REST API (WP-API) 2.0-beta2 or later.
* Requires SearchWP Version 2.6 or later

### Example Queries
* `wp-json/swp_api/search?s=jedi&egnine=star-wars`
* `wp-json/swp_api/search?&tax_query[field]=slug&tax_query[taxonomy]=categories&tax_query[terms]=1`
* `wp-json/swp_api/search?meta_query[key]=jedi&meta_query[value]=luke&tax_query[compare]=IN`

### License & Copyright
* Copyright 2015  Josh Pollock for CalderaWP LLC.

* Licensed under the terms of the [GNU General Public License version 2](http://www.gnu.org/licenses/gpl-2.0.html) or later.

* Please share with your neighbor.

