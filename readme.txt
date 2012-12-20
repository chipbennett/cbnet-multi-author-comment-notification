=== cbnet Multi Author Comment Notification ===
Contributors: chipbennett
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QP3N9HUSYJPK6
Tags: cbnet, multi, author, comment, comments, comment notification, notification, notify, admin, administrator, email, maxblogpress
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 2.1

Send comment notification emails to multiple users. Select users individually or by user role, or send emails to arbitrary email addresses.

== Description ==

Easily enable email notification of new comments to users other than the post author.

Via Dashboard -> Settings -> Discussion, enable email notification to users by user role (Administrator, Editor, Author, Contributor, Subscriber ), or define arbitary email addresses to notify. Also, optionally disable email notification for comments left by registered users.

Email notification for individual users can be enabled via each user's profile.

== Installation ==

Manual installation:

1. Upload the `cbnet-multi-author-comment-notification` folder to the `/wp-content/plugins/` directory

Installation using "Add New Plugin"

1. From your Admin UI (Dashboard), use the menu to select Plugins -> Add New
2. Search for 'cbnet Multi Author Comment Notification'
3. Click the 'Install' button to open the plugin's repository listing
4. Click the 'Install' button

Activiation and Use

1. Activate the plugin through the 'Plugins' menu in WordPress
2. From your Admin UI (Dashboard), use the menu to select Settings -> Discussion
3. Configure settings, and save
4. To enable comment notification for individual users, configure "Comment Email Notification" on each user's profile

== Frequently Asked Questions ==

= Where did settings go? =

Plugin settings can be found under Dashboard -> Settings -> Discussion.

Comment email notification for individual users can be configured via the user profile.

Let me know what questions you have!

== Screenshots ==

Screenshots coming soon.


== Changelog ==

= 2.1 =
* Made Plugin translation-ready
= 2.0.2 =
* Bugfix
** Fix bug with settings validation callback not accounting for single email address
= 2.0.1 =
* Bugfix
** Wrap pluggable function in function_exists() conditional.
= 2.0 =
* Major update
** Plugin completely rewritten
** Settings API implementation
** Move Plugin settings from custom settings page to Settings -> Discussion
** Add custom user meta for individual user email notification
** Implement via pluggable function wp_notify_postauthor()
** Made Plugin parameters filterable
** Removed all cruft code
** WARNING: Old settings will not be retained
= 1.1.2 =
* Bugfix update
* PHP shorttag fixed on line 249. Props Otto42
* isset conditional added for email on line 244. Props Otto42.
= 1.1.1 =
* Readme.txt update
* Updated Donate Link in readme.txt
= 1.1 =
* Initial Release
* Forked from MaxBlogPress Multi Author Comment Notification plugin version 1.0.5


== Upgrade Notice ==

= 2.1 =
Made Plugin translation-ready
= 2.0.2 =
Bugfix. Fix bug with settings validation callback not accounting for single email address.
= 2.0.1 =
Bugfix. Wrap pluggable function wp_notify_postauthor() in function_exists() wrapper for activation.
= 2.0 =
Major update. Plugin completely rewritten. WARNING: Previous settings will not be retained.
= 1.1.2 =
Bugfix. Two minor PHP notices fixed.
= 1.1.1 =
Readme.txt update. Updated Donate Link in readme.txt.
= 1.1 =
Initial Release. Forked from MaxBlogPress Multi Author Comment Notification plugin version 1.0.5.
