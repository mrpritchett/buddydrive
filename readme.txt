=== BuddyDrive ===
Contributors: mrpritchett, imath
Tags: BuddyPress, files, folders
Requires at least: 4.5
Tested up to: 4.7.0
Stable tag: 2.1.1
License: GPLv2

Share files the BuddyPress way!

== Description ==

As a plugin for BuddyPress, BuddyDrive allows community members to share files or folders with ease. Via the BP attachment API, BuddyPress makes sharing content possible in a variety of ways, including:

* Owner only (private)
* Password protected content
* Owner and friends
* Owner and specified groups
* Owner and specified members
* Public sharing (everybody)

BuddyPress is available in English, French, Dutch and Brazilian Portuguese.

== Installation ==

You can download and install BuddyDrive using the built in WordPress plugin installer. If you download BuddyDrive manually, make sure it is uploaded to "/wp-content/plugins/buddydrive/".

Activate BuddyDrive in the "Plugins" admin panel using the "Network Activate" if you activated BuddyPress on the network (or "Activate" if you are not running a network, or if BuddyPress is activated on a subsite of the network) link.

== Frequently Asked Questions ==

= Help! No files show up. What do I do? =

Upgrade to version 2.1.1 and run the database upgrade.

= How do you upload files in BuddyDrive from the front end? =

Click the icon of a file with a plus icon to add a file. You can then click the button to find a file or drag and drop a file into the selected area.

= How do you delete files? =

Click the edit button in the control panel. Select the file or files you wish to delete. Click the Remove button.

= Can you use video and mp3 files?  =

Yes. Multiple file types are allowed in BuddyDrive. You can edit which filetypes are allowed on the BuddyDrive settings page in the WordPress dashboard.

= Where can I find information about bugs, quick fixes, common problems, etc? =

http://wpbuddydrive.com

= Is there a limit to how many people I can share files with? =

No, you can share access with as many people as you desire.

= Can I have more than one folder or folders within folders? =

No, currently only one level of directories is allowed.

= Can you add ____ feature? =

We love hearing your needs, want, desires, and ideas for BuddyPress! Our goal is to provide the product that you need. This questions helps us know what you are looking for in BuddyPress and we try to accommodate these requests via updates as much as possible.

== Screenshots ==

1. File shared into multiple groups.
2. BuddyDrive Uploader.
3. File shared with specific members.
4. Content of a shared folder.
5. BuddyDrive items administration.

== Changelog ==

= 2.1.1 =
* Fixes issue where upgrader wanted to run endlessly.
* Fixes Freemius integration issue.

= 2.1.0 =
* Plugin changed ownership, updated files to reflect this.
* Integrated Freemius Insights to better serve users.
* Fixed issue where database upgrade wasn't possible, thereby breaking plugin on upgrade to 2.x.x series.

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
