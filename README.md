![Banner](https://calderawp.com/wp-content/uploads/2015/06/WPORG_SearchWP.png)

SearchWP API
============

Adds an endpoint to the WordPress REST API for searching via [SearchWP](https://searchwp.com/?ref=121) -- the best tool for improving the usefulness and performance of WordPress search.

This plugin is a free plugin by [CalderaWP](https://CalderaWP.com). It is not an official add-on for SearchWP and is no way associated with SearchWP or the National Football League.

* Requires WordPress REST API (WP-API) 2.0-beta9 or later or WordPress 4.4 or later.
* Requires SearchWP Version 2.6 or later

Technically will work without SearchWP, but queries will run through WP_Query.

### Example Queries
For a complete list of possible queries, see: [https://calderawp.com/doc/searchwp-api-queries/](https://calderawp.com/doc/searchwp-api-queries/)
* `wp-json/swp_api/search?s=jedi&egnine=star-wars`
* `wp-json/swp_api/search?&tax_query[field]=slug&tax_query[taxonomy]=categories&tax_query[terms]=1`
* `wp-json/swp_api/search?meta_query[key]=jedi&meta_query[value]=luke&tax_query[compare]=IN`

### Multiple Meta Queries
Nested meta queries added in version 1.1.0 and allow for querying by multiple meta fields. Be sure to see the [relevant section of the WP_Query docs](https://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters)


##### Simple Nested Query
Use a `[]` to create a basic nested query. Default meta_relation is AND. Default compare is IN.

Example: Search where jedi field is "luke" and sith field is "vader".

URL String:  `/wp-json/swp_api/search?meta_query[key][]=jedi&meta_query[value][]=luke&meta_query[key][]=sith&meta_query[value][]=vader`

Translates to:
```php
'meta_query' => array (
  'relation' => 'AND',
  array (
    'key' => 'jedi',
    'value' => 'luke',
    'compare' => 'IN',
  ),
  array (
    'key' => 'sith',
    'value' => 'vader',
    'compare' => 'IN',
  ),
)
```

##### Relationships Between Query Parts
Use variable `meta_relation` to change relationship been queries. Also, you can set compare, like with regular meta_queries. Make sure your compare argument is in the right order.

Example: Search where jedi field is "luke" or sith field is not "vader".

URL String `wp-json/swp_api/search?meta_query[key][]=jedi&meta_query[value][]=luke&meta_query[compare][]=in&meta_query[key][]=sith&meta_query[value][]=vader&meta_query[compare][]=NOT%20IN&meta_relation=OR`

Translates to:
```php
'meta_query' => array (
  'relation' => 'OR',
  array (
    'key' => 'jedi',
    'value' => 'luke',
    'compare' => 'IN',
  ),

  array (
    'key' => 'sith',
    'value' => 'vader',
    'compare' => 'NOT IN',
  ),
)
```

##### Multiple Values In A Query Part
Put multiple fields into your query parts, by adding extra nesting levels in URL string. Order gets very tricky, numbers (remember to start at 0) help, as shown below.

Example: Search where jedi field is "luke" or "obi-wan" and sith field is not "vader".

URL STRING: `wp-json/swp_api/search?meta_query[key][]=jedi&meta_query[value][0][]=luke&meta_query[value][0][]=obi-wan&meta_query[compare][]=in&meta_query[key][]=sith&meta_query[value][]=vader&meta_query[compare][]=NOT%20IN&meta_relation=AND`
```php
array (
  'relation' => 'AND',
  array (
    'key' => 'jedi',
    'value' => array ('luke', 'obi-wan' ),
    'compare' => 'IN',
  ),
  array (
    'key' => 'sith',
    'value' => 'vader',
    'compare' => 'NOT IN',
  ),
)
```


### License & Copyright
* Copyright 2015  Josh Pollock for CalderaWP LLC.

* Licensed under the terms of the [GNU General Public License version 2](http://www.gnu.org/licenses/gpl-2.0.html) or later.

* Please share with your neighbor.

