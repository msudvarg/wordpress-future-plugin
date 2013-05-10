wordpress-future-plugin
=======================

=== Plugin Name ===
Contributors: msudvarg
Donate link: http://peaceeconomyproject.org/wordpress/?page_id=23
Tags: msudvarg, future, schedule, scheduled, future posts, scheduled posts, calendar, calendar widget, calendar category, category calendar, events, event posts, future events, event calendar, events calendar
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates future-dated posts into your blog. Adds future posts and category selection to Wordpress's built-in calendar widget.

== Description ==

The 'Future' plugin allows posts with future scheduled dates to be integrated into a site. This can be useful, for example, with events that have associated dates in the future. Such future posts can, with this plugin, be displayed, both individually and in archive lists. This plugin also adds functionality to the built-in calendar widget. It adds a checkbox to include future posts in the calendar, and it allows the calendar to be configured to show posts from a single category.

== Installation ==

1. Upload 'future.php' to the /wp-content/plugins/ directory
2. Go to the Plugins page in your WordPress Admin area and click 'Activate' for Future

== Frequently Asked Questions ==

= How to show future/scheduled posts? =

On your page call, add the get variable 'future' and set this to true. For example: "http://example.com/?cat=7&future=true" or "http://example.com/blog/2013/?future=true"

This displays all future posts in ascending order of date.

= What if I want to display both past and future posts? =

On your page call, set the variable 'future' to all. For example: "http://example.com/?cat=7&future=all" or "http://example.com/blog/2013/?future=all" 

This displays all posts (both published and scheduled) in descending order of date.

= What if I want my Wordpress calendar widget to display my future/scheduled posts? =

This plugin modifies the Wordpress calendar widget so it now has a checkbox to "Include Future Posts." Simply check this box and the calendar will display all posts, including future/scheduled posts.

= I only want future posts from one category to show up in my calendar. How do I do this? =

With this plugin, the Wordpress calendar widget also has a dropdown box to select a single category. You can select a category with or without checking the "Include Future Posts" option, and the calendar will limit the posts it shows to this category.

== Changelog ==

= 1.1.2 =
* Fixed a bug where linked dates in the calendar widget do not have post titles as link titles. Now hovering over dates in the calendar displays the relevant post titles from that day.

= 1.1.1 =
* Fixed a bug where the calendar widget's "next" navigational link navigated to the previous, rather than the next, month

= 1.1 =
* Updated calendar widget code to more closely match Wordpress's default calendar widget code.
* Fixed the calendar widget navigational links to be compatible with blogs that utilize non-default, nice-looking permalinks (e.g. /blog/2013 instead of /?m=2013).
* Fixed a problem where deactivating/activating the plugin would remove the calendar widget from any widget areas where it was active

= 1.0.1 =
* 1.0.1 fixes a bug from 1.0 where selecting "All Categories" in the calendar widget made the widget show no posts.

= 1.0 =
* 1.0 is the first public release of the Future plugin, after having been developed and tested in its various versions on the Peace Economy Project's website (http://www.peaceeconomyproject.org/).

== Upgrade Notice ==

= 1.1.2 =
1.1.2 fixes a bug where linked dates in the calendar widget don't have associated post titles as link titles

= 1.1.1 =
1.1.1 is an essential upgrade to fix problems with the calendar widget.

= 1.1 =
1.1 is an essential upgrade if you use the calendar widget on a site that utilizes non-default permalinks.

= 1.0.1 =
1.0.1 is an essential upgrade that fixes a bug from 1.0 where selecting "All Categories" in the calendar widget made the widget show no posts.

= 1.0 =
1.0 is the first public release of the Future plugin, after having been developed and tested in its various versions on the Peace Economy Project's website (http://www.peaceeconomyproject.org/).

== Screenshots ==

1. New Calendar Widget with Category and "Include Future Posts" Checkbox
2. This screenshot was taken March 17, 2013. Notice that the April events are displayed and the calendar widget shows only the Events category for April 2013.
