=== Custom Taxonomies Menu Widget ===

Version: 1.3.1
Author: Ade Walker
Author page: http://www.studiograsshopper.ch
Contributors: studiograsshopper
Plugin page: http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/
Donate link: http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/
Tags: custom taxonomies,taxonomy, menu, widget
Requires at least: 3.8
Tested up to: 3.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.3.1

Creates a simple menu of your custom taxonomies and their associated terms, ideal for sidebars. Highly customisable via widget control panel.


== Description ==

Creates a simple menu of your custom taxonomies and their associated terms, ideal for sidebars. Highly customisable via checkboxes to select which custom taxonomies and terms are displayed in the menu.

**Key Features**
----------------

* Select which custom taxonomies to display
* Select which terms to display within the selected custom taxonomies
* Choose the order in which terms are displayed within the custom taxonomies (ID, name, count, etc)
* Choose whether to display the taxonomy name as a title
* Choose whether to display the list of terms as a hierarchy
* Choose whether to hide terms with no posts
* NEW - User options to control how to treat display of new terms added to a taxonomy


**Further information**
-----------------------
Comprehensive information on configuring and using the plugin can be found here:

* [Configuration Guide](http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/configuration/)
* [FAQ](http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/faq/)



== Installation ==

Either use the WordPress Plugin Installer (Dashboard > Plugins > Add New, then search for "custom taxonomies menu widget"), or manually as follows:

1. Download the latest version of the plugin to your computer.
2. Extract and upload the folder *custom-taxonomies-menu-widget* to your */wp-content/plugins/* directory. Please ensure that you do not rename any folder or filenames in the process.
3. Activate the plugin in your Dashboard via the "Plugins" menu.
4. Go to the Dashboard > Appearance > Widgets page, where you can now see the Custom Taxonomies Menu Widget in the available widgets, ready for use in any of your theme's widget areas.

Note for WordPress Multisite users:

* Install the plugin in your */plugins/* directory (do not install in the */mu-plugins/* directory).
* In order for this plugin to be visible to Site Admins, the plugin has to be activated for each blog by the Network Admin.

**Upgrading from a previous version**
-------------------------------------

You can use the Wordpress Automatic Plugin upgrade link in the Dashboard Plugins menu to automatically upgrade the plugin.



== Frequently Asked Questions ==

= Where can I get Support? =

Further information about setting up and using the plugin can be found in the plugin's [Configuration Guide](http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/configuration/).

If, having read the information linked to above, you cannot solve your issue, or if you find a bug, you can post a message on the plugin's [Support Forum](http://wordpress.org/support/plugin/custom-taxonomies-menu-widget).

Support is provided in my free time but every effort will be made to respond to support queries as quickly as possible.


= Can I Donate? =

Yes, of course you can! You can find a link [here](http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/). Thanks!


= Terms Handling options =

New widget control panel option in version 1.3.

This option determines how the plugin should treat new terms added to a taxonomy since the last time the widget options were Saved by the user. Best option for most users will be **Auto top level / smart child**. To replicate pre-1.3 behaviour, select **Auto**.

**Auto**

Any new Top Level or Child term created since last Save is:

* automatically included in Menu
* automatically shown as checked in the widget control panel


**Manual**

Any new Top Level or Child term created since last Save is:

* not included in Menu
* not shown as checked in the widget control panel

Note: User always has to open widget control panel to manually add terms to the Menu


**Auto top level / smart child**

Any new Top Level term created since last Save is:

* automatically included in Menu
* automatically shown as checked in the widget control panel

Any new Child term created since last Save is:

* only included in Menu if its parent is already included
* only shown as checked in widget control panel if its parent is checked

This means that a new child of a new top level term will be checked and included in Menu.

On Save, any checked child terms, whose parents have been unchecked in the widget control panel, will be automatically unchecked. In other words, to check a child term, you must also check its parent.


**Manual top level / smart child**

Any new Top Level term created since last Save is:

* not included in Menu
* not shown as checked in the widget control panel

Any new Child term created since last Save is:

* only included in Menu if its parent is already included
* only shown as checked in the widget control panel if its parent is checked

This means that a new child of a new top level term will not be checked and won't be included in Menu.

On Save, any checked child terms whose parents have been unchecked in control panel will be automatically unchecked. In other words, to check a child term, you must also check its parent.

To replicate pre-1.3 behaviour, select **Auto**. However, note that many users may find the new **Auto top level / smart child** option to be the better option.

Further information and examples can be found in the plugin's [Configuration Guide](http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/configuration/).


= Why does the widget panel re-check all terms that I've just unchecked? =

If you uncheck all terms in a taxonomy (in the widget's control panel), all terms will be automatically checked on Save. This is intentional behaviour.

If you want to hide a taxonomy's terms, uncheck the taxonomy itself.


= How to include builtin taxonomies =

The Custom Taxonomies Menu Widget is designed to display "custom" taxonomies - hence the title. However, some users have been nagging me to include builtin taxonomies, ie Category and Tag.

In response to these nags, version 1.3 now provides the 'sgr_ctmw_taxonomies' filter which can be used to filter the $args sent to the get_taxonomies() function used by the plugin. The filter passes an array called $args.


= License and Disclaimer =

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

The license for this software can be found here: [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)

Thanks for downloading the plugin.  Enjoy!



== Using the plugin ==

Go to your Dashboard > Appearance > Widgets page and drag the Custom Taxonomies Menu widget to your sidebar. Open the widget control panel and configure the options.



== Upgrade Notice ==

= 1.3 =

Version 1.3 introduces new options for determining how the plugin should treat new terms added to a taxonomy since the last time the widget options were Saved by the user. These "Terms Handling" options are set to "Auto" by default, which replicates the way prior versions of the plugin included new terms.

Added 'sgr_ctmw_taxonomies' filter which can be used to filter the $args sent to the get_taxonomies() function used by the plugin. The filter passes an array called $args. This allows you to add in built-in taxonomies if you wish to to do so.



== Screenshots ==
1. Custom Taxonomies Menu Widget control panel



== Changelog ==

= 1.3.1 =
* Released 16 December 2013
* Enhance: Wp 3.8 minimum version required
* Bug fix: Tweaked admin CSS to fix new-look WP 3.8 Dashboard styles

= 1.3 =
* Released 6 May 2013
* Enhance: Added new control panel options for Terms Handling, which determine how the plugin should treat new terms added to a taxonomy since the last time the widget options were Saved by the user
* Enhance: Major re-write to integrate options and to treat new terms added to a taxonomy since last Save
* Enhance: SGR_CTMW_DOMAIN deprecated
* Bug fix: Changed textdomain to string 'sgr-ctmw', no longer Constant
* Bug fix: Improved sanitisation/validation when widget options are saved

= 1.2.2 =
* Readme.txt updated 1 April 2013
* Released 1 January 2012
* Bug fix: Fixed PHP data type error

= 1.2.1 =
* Released 29 December 2011
* Bug fix: Fixed PHP error on upgrade with $known_terms returning NULL

= 1.2 =
* Released 28 December 2011
* Bug fix: Now ignores taxonomies with no terms
* Bug fix: Fixed missing internationalisation for strings in the widget form
* Bug fix: Enqueues admin CSS properly now
* Enhance: Upped minimum WP version requirement to 3.2 - upgrade!
* Enhance: Widget sub-class now uses PHP5 constructor
* Enhance: Widget control form made wider, less scrolling required for long taxonomy checklist
* Enhance: Added activation hook function for WP version check, deprecated SGR_CTMW_WP_VERSION_REQ constant
* Enhance: sgr_ctmw_wp_version_check() deprecated
* Enhance: sgr_ctmw_admin_notices() deprecated
* Enhance: Added SGR_CTMW_HOME for plugin's homepage url on studiograsshopper
* Enhance: Plugin files reorganised, sgr-ctmw-admin-core.php no longer used
* Feature: 'hide_empty' options added, to allow display of empty terms in the menu
* Feature: New terms are now automatically added to menu, and 'checked' in the widget form

= 1.1.1 =
* Released 1 March 2011
* Bug fix: Removed debug code form sgr_ctmw_wp_version_check() function

= 1.1 =
* Released 4 November 2010
* Feature: Added option to hide Taxonomy title
* Feature: Added option to select whether or not to display terms as a hierarchy

= 1.0 =
* Public release 9 October 2010