=== Admin Flush W3TC Cache ===
Contributors: texastoast78
Donate link: 
Tags: admin, cache, flush, w3tc, W3 Total Cache, user experience, caching, page cache, css cache, js cache, db cache, disk cache, disk caching, database cache, http compression, gzip, deflate, minify, cdn, content delivery network, media library, performance, speed, multiple hosts, css, merge, combine, unobtrusive javascript, compress, optimize, optimizer, javascript, js, cascading style sheet, plugin, yslow, yui, google, google rank, google page speed, mod_pagespeed, s3, cloudfront, aws, amazon web services, cloud files, rackspace, cotendo, max cdn, limelight, cloudflare, microsoft, microsoft azure, iis, nginx, apache, varnish, xcache, apc, eacclerator, wincache, mysql, w3 total cache, batcache, wp cache, wp super cache, buddypress
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: trunk

Admin Flush W3TC Cache works with the W3 Total Cache plugin.  It simply adds an "Empty All Caches" option to every Admin page.

== Description ==

* Adds "Empty All Caches" link to the top of every Admin page.</li>
* Clicking the link clears all caches and returns you to your current page.</li>
* No need to go to Performance options anymore, just clear the cache from wherever you are.</li>
* Requires the [W3 Total Cache plugin](http://wordpress.org/extend/plugins/w3-total-cache/ "Get W3 Total Cache"), so make sure it is installed and activated.


== Installation ==

1. Upload the `admin-flush-w3tc-cache` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Where is the "Empty All Caches" button? =

It's not a button, it's a text link.  Assuming the plugin is enabled and working properly, the link should appear at the top right of your screen (for LTR languages, left side for RTL languages).

= How do I use it? =

Click the link from any admin page and all caches will be cleared.

You will be returned to the same page you were on when you clicked the link, and you should see the standard W3TC notification that the caches have been cleared.

= Where are the settings for Admin Flush W3TC Cache? =

There are no settings, just enable the plugin and the link appears.

= The "Empty All Caches" link is not appearing on my admin pages? =

Admin Flush W3TC Cache requires that the W3 Total Cache plugin be installed and active.  If either of these conditions is not true, the link will not appear.  Please ensure that you have installed the W3 Total Cache plugin and that it has been activated.

= The "Empty All Caches" link redirects me to the W3TC General Options page and doesn't clear the cache. =

Update to Admin Flush W3TC Cache version 1.2 or greater.  Version 1.2 fixes compatibility with W3TC 0.9.2.4 and above.

== Screenshots ==
1. WordPress Dashboard with "Empty All Caches" link top right.
2. WordPress Edit Page screen after clearing caches with confirmation message.

== Changelog ==

= 1.2 =
* Compatibility with W3TC 0.9.2.4
* FIX - Redirects to W3TC General Options without clearing caches in W3TC 0.9.2.4 (added nonces).

= 1.1 =
* Updated Documentation, code comments.

= 1.0 =
* Initial Release.

== Upgrade Notice ==

= 1.0 =
* Initial Release