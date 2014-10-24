=== BuddyDrive ===
Contributors: imath
Donate link: http://imathi.eu/donations/
Tags: BuddyPress, files, folders
Requires at least: 3.9
Tested up to: 4.0
Stable tag: 1.2.1
License: GPLv2

Share files the BuddyPress way!

== Description ==

BuddyDrive is a BuddyPress plugin (it requires at least version 2.0) that uses WordPress built in features for the management of its post attachments to allow the members of a community to share a file or a list of files thanks to the BuddyDrive folders.
Depending on the BuddyPress settings, the access to the BuddyDrive user's content can be restricted to :

* the owner of the item only,
* people that know the password the owner set for his item,
* the friends of the owner of the file,
* the members of the group the content is attached to,
* or everybody !

This plugin is available in english and french.

http://vimeo.com/64323101

== Installation ==

You can download and install BuddyDrive using the built in WordPress plugin installer. If you download BuddyDrive manually, make sure it is uploaded to "/wp-content/plugins/buddydrive/".

Activate BuddyDrive in the "Plugins" admin panel using the "Network Activate" if you activated BuddyPress on the network (or "Activate" if you are not running a network, or if BuddyPress is activated on a subsite of the network) link.

== Frequently Asked Questions ==

= If you have any question =

Please add a comment <a href="http://imathi.eu/tag/buddydrive/">here</a>

== Screenshots ==

1. User's BuddyDrive.
2. BuddyDrive Uploader
3. BuddyDrive embed file.
4. BuddyDrive Supervising area.
5. BuddyDrive settings page

== Changelog ==

= 1.2.1 =
* Fixes a problem when sharing folders in the activity stream
* Improves translation by better localizing javascript files
* Fixes a notice error in the BuddyDrive Administration
* Fixes a notice error in case the file does not exist anymore

= 1.2.0 =
* Adapts to changes introduced in plupload feature by WordPress 3.9
* List all user's groups setting the per_page argument to false
* Improves javascripts and corrects some bugs in this area
* Uses dashicons
* Improves theme "adaptability" using BuddyPress members/single/plugins.php template and a new screen class
* Allowes the use of custom fields for BuddyDrive Files
* Adds an action to track downloads
* Adds filters to add a custom column in WP List Table
* Enjoys the "2.0" BuddyPress wp-admin/profile to display user stats
* Adds a filter to change the upload dir
* Adds a select box to sort items by last edit dates or names
* Removes the Network:true tag and uses specific checks to be sure BuddyDrive shares the same "config" than BuddyPress
* Adds a link in BuddyDrive's groups pages to open current user's BuddyDrive explorer

= 1.1.1 =
* fixes a bug reported by a user in BuddyDrive files and folders admin (Checks WP_List_Table)
* Modifies the way BuddyDrive group extension is loaded (waits for bp_init to be sure group id is set)

= 1.1 =
* fixes the bug with hidden groups.
* brings cutomizable slugs and names.
* brings more control over users upload quota (by role or even by user).
* adds an information in network administration users list or blog administration users list. 
* BuddyDrive can now be automatically activated for newly created groups.
* tested in BuddyPress 1.8 & still requires at least version 1.7

= 1.0 =
* files, folders management for users
* Requires BuddyPress 1.7
* language supported : french, english

== Upgrade Notice ==

= 1.2.1 =
As usual, backup your database before upgrading

= 1.2.0 =
!Important: Requires WordPress 3.9 & BuddyPress 2.0. You really should backup your database before upgrading, as some changes occured in the way BuddyDrive loads.

= 1.1.1 =
Nothing particular, but just in case, you should make a db backup before upgrading. Requires at least BuddyPress 1.7. Tested in BuddyPress 1.8.1

= 1.1 =
Nothing particular, but just in case, you should make a db backup before upgrading. Requires at least BuddyPress 1.7. Tested in BuddyPress 1.8.

= 1.0 =
first version of the plugin, so nothing particular.
