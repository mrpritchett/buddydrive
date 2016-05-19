=== BuddyDrive ===
Contributors: imath
Donate link: http://imathi.eu/donations/
Tags: BuddyPress, files, folders
Requires at least: 4.5
Tested up to: 4.5.2
Stable tag: 2.0.0
License: GPLv2

Share files the BuddyPress way!

== Description ==

BuddyDrive is a BuddyPress (2.5+) plugin that uses the BP Attachment API to allow the members of a community to share files or folders. Depending on the BuddyPress settings, the access to the BuddyDrive user's content can be restricted to:

* the owner of the item only (private),
* people that know the password the owner set for his item (protected),
* the friends of the owner of the file (Restricted to friends),
* the members of the group the content is attached to (Restricted to groups),
* specific members the owner selects (Restricted to members),
* or everybody (public) !

This plugin is available in english and french.

https://vimeo.com/167316544

== Installation ==

You can download and install BuddyDrive using the built in WordPress plugin installer. If you download BuddyDrive manually, make sure it is uploaded to "/wp-content/plugins/buddydrive/".

Activate BuddyDrive in the "Plugins" admin panel using the "Network Activate" if you activated BuddyPress on the network (or "Activate" if you are not running a network, or if BuddyPress is activated on a subsite of the network) link.

== Frequently Asked Questions ==

= If you have any question =

Please add a comment <a href="http://imathi.eu/tag/buddydrive/">here</a>

== Screenshots ==

1. File shared into multiple groups.
2. BuddyDrive Uploader.
3. File shared with specific members.
4. Content of a shared folder.
5. BuddyDrive items administration.

== Changelog ==

= 2.0.0 =
* !important Requires WordPress 4.5 and BuddyPress 2.5.
* Use custom post status to set a file or folder privacy.
* Completely revamped UI (Backbone/Underscore).
* Multiple file uploads.
* Share a file into multiple groups.
* Directly upload new files or create new folders from the current group.
* Share files or folders with specific members you select.
* Real shared folders.
* Search.
* Detailed user statistics.

= 1.3.4 =
* Prevents an error when the downloaded file size is bigger than the WP memory limit (props bentasm1).
* Improve the way the Groups selectbox is populated and include a new filter to eventually restrict the groups the user can share an item into.

= 1.3.3 =
* Add new hooks to allow custom privacy settings.
* Fix 2 notice errors.
* Fix an english error.

= 1.3.2 =
* Fix a regression introduced in 1.3.1 when opening folders shared in groups.
* Introduce a new filter to customize the main BuddyDrive query.
* Make sure custom fields are shown when editing files.

= 1.3.1 =
* Allow custom loop to list files according to the files privacy
* Allow BuddyDrive's group nav item visibility/access to be overriden
* Adapt to WP List Table changes introduced in WordPress 4.3
* Use the correct filter for the type being saved in BuddyDrive_Item->save()

= 1.3.0 =
* Requires BuddyPress 2.3
* Uses the BuddyPress Attachments API to manage user submitted media.
* Introduces the BuddyDrive editor, a specific tool plugins can use to attached file to their content.
* Updates the group's latest activity when a file is shared with the members of the group.
* Improve the Bulk delete feature in the Administration screen to make sure each user's quota will be updated.
* Now includes a thumbnail for public image files when embed in the activity stream, a private message, a post, a page, ...

= 1.2.3 =
* Spanish translation, props sr_blasco
* Make sure add_query_arg() urls are escaped.

= 1.2.2 =
* Now Administrators can view users private files and folders on front-end
* Improves translation by allowing the mo file to be out of the plugin's directory
* Fixes a notice error using the BuddyPress Group Extension API
* Fixes a notice error when the BuddyDrive main page is displayed (eg: password form)
* Removes accents from filename when saving a file to avoid char encoding troubles
* Makes sure the quota and other upload restrictions are doing their job
* Introduces some filters to add new upload restrictions

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

= 2.0.0 =
Very important: backup your database before upgrading. Requires WP 4.5 & BP 2.5.

= 1.3.4 =
As usual, backup your database before upgrading

= 1.3.3 =
As usual, backup your database before upgrading

= 1.3.2 =
As usual, backup your database before upgrading

= 1.3.1 =
As usual, backup your database before upgrading

= 1.3.0 =
As usual, backup your database before upgrading

= 1.2.3 =
As usual, backup your database before upgrading

= 1.2.2 =
As usual, backup your database before upgrading

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
