=== Backup Plugin by blogVault ===
Name: Backup Plugin by blogVault
Contributors: Backup by blogVault, akshatc, singhsivam
Tags: backup, backup plugin, backups, wordpress backups, wordpress backup, wordpress backup plugin, database backup, complete backup, wp backup, automatic backup, backup wordpress, theme backup, database backup
Donate link: http://blogvault.net
Requires at least: 1.5
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

blogVault is the best wordpress backup plugin. It makes it really easy restore, migrate, or secure your sites from the backups.

== Description ==

[blogVault](http://blogvault.net?bvsrc=wpplugin_readme) is a top-notch WordPress backup plugin that creates daily automatic backups of your WordPress site. Backup your entire site, including files and database, with this easy to use backup plugin.

**Top Features**:

1. **Daily automatic backups** for your entire site content, including Posts, Pages, Plugin, Images, Comments, etc.

2. Supports the 3 pillars that make a good wordpress backup plugin - backup, restore, and migration

3. Maintains **multiple copies** of backups on its own as well as Amazon S3 servers

4. **Real-time backup** so that no data is ever lost.

5. Upload any chosen version of your backup to your Dropbox account using the **Upload to Dropbox** feature

= Easy Setup =
It takes only a few minutes to install the blogVault backup plugin. blogVault schedules daily backups and only uses offsite storage, so everything is automatically set up. The first backup is initiated immediately and you can view the progress of your backups from the blogVault dashboard.

= Managed offsite backups =
Like in the case of any good wp backup plugin, blogVault supports both file and database backups so that your entire wordpress site is protected at all times. It creates offsite backups so that your content is safe even if your site goes down. It manages the backup archive and stores up to 30 wordpress backups at any point of time. The backups are stored in blogVault's own servers and then further copied to Amazon S3. Hence 9 copies of the backup are maintained across multiple independent data-centers.
Many regular backup plugins store backups locally on the server. This puts additional load on the server and slows your site down. Not only that, this backup technique can quickly eat up all your server storage space if you have a limited plan. The blogVault backup plugin doesn’t use any local storage for your backups. Finally, local backups are as good as no backups at all. If the server crashes then you will lose the WordPress site and the backups too.

= Easy Restore of Backups =
If your site gets hacked, blogVault's easy restore feature helps you get back on your feet swiftly. It automatically restores a specific WordPress backup onto the server.
blogVault also includes a test-restore feature using which you can test backups before deploying them onto the server or simply to verify the integrity of a backup version. The wp backup is temporarily restored on blogVault’s own servers so that you can validate the backup.

= Migration using Backup =
blogVault’s simple migration feature helps you move to a new domain or host seamlessly using backups. The chosen backup version is uploaded onto the new location with just a few clicks and your site is ready to be launched. Any version from the list of backups can be used for this. The migration takes place straight from the backup stored on the blogVault servers, hence not affecting the original site. blogVault does migrations in parts, and hence very large backups can be migrated easily.

= Securing your Backup =
blogVault provides the best security by maintaining multiple copies of backups on its own as well as Amazon S3 servers. This ensures redundancy of your wp backups. All your backups are encrypted and are hence 100% safe with us.
You can also use the backup to Dropbox feature and upload backups to your Dropbox account. This feature enables you to store chosen versions of your WordPress backup beyond the 30 days for which blogVault archives your backups.

= Incremental Backups =
Do you own large site that spans 10s of GB? Then regular backup plugins may not work for you. blogVault does a complete backup of your site the very first time. After this, only the changes since the last backup are picked up. This backup method, known as incremental backup, reduces the load on your server and the size of your backups.

= Real-time Backup =
With a regular backup plugin, there is a possibility of losing data even if you have daily scheduled backups. Sites that deal with e-commerce or news undergo multiple updates in a day. An outage in the middle of the day could spell doom. blogVault’s real-time backup ensures that any change is immediately saved with this instant backup. You no longer have to worry about losing a single transaction, even if your site crashes in between scheduled backups. blogVault also has special handling for wooCommerce sites which regular backup plugins can’t handle.

= WordPress Multisite(WPMU) Backup =
blogVault supports WordPress Multisite backups. The entire multi-site can be backed up, migrated or restored. Some other services backup only a single subsite. However, we will backup the entire network, hence reducing the possibility of any shared resource such as plugin or theme to be lost.

= Backup Monitoring =
Does your backup plugin verify the integrity of your backups? blogVault monitors the scheduled backups and notifies you immediately when an issue arises with your backups.

= Backup History =
An easy way of managing backups is very essential for any backup plugin. Assuming that you take daily backups of your site, we are talking about 30 a month, 360 a year, and so on. A good history page that lists all the backups is very useful. However, if you want to look for a specific backup version, how do you do it? If you want to rollback your site to backup containing specific changes, is there a way?
blogVault’s history page includes all the details for each backup like list of plugins, number of posts, pages, files, and tables. Not only that, it also highlights the changes in a backup making it really easy to locate any specific updates. It also includes a screenshot for every backup which again enables you to find a particular backup with a single glance.

= Test-Restore the Backup =
blogVault Provides a unique feature which lets you restore the backup temporarily on our test servers. This lets you validate the backup and ensure that the backup is perfect. It can also be used to identify the right backup to restore from in case you are reverting to an older backup.

= Support =
blogVault provides the best technical support in comparison to other wordpress backup plugins, using phone, email and chat. If you face any issues with backups, the support team is always ready to help.

= Backup Plans =
blogVault provides 3 different backup plans – basic, plus, and pro, to suit different backup needs. Whether you are a small time entrepreneur or a large company, blogVault puts an end to your data loss worries with these flexible backup plans. blogVault includes a 7 day free trial so that you can explore all the features of this backup plugin before committing to a specific backup plan.
Your WordPress backups are as valuable as your site itself. Without a good backup in place, you are a risk of losing everything when your site goes down. It is very important to choose a backup plugin that brings the best backup features and guaranteed support. The blogVault backup plugin is the best choice for you.

[vimeo http://vimeo.com/88638675]

== Installation ==

= Automatic installation =

* The backup plugin can be installed automatically just like any other. To do an automatic install of blogVault backup plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.
* In the search field type **Backup plugin by blogVault** and click Search Plugins. Once you’ve found our WordPress backup plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.
* After installation, click **Activate Plugin**. The last and final step is to register with us. Enter the fields in blogVault’s user form and click **Register**. Your site is ready for backup!

= Manual installation =

The backup plugin can also be installed manually by downloading it from the WordPress plugin repository and then uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation). Once completed, follow the same instructions as above to register with us and obtain a license key. Your backup should be initiated immediately.

== Frequently Asked Questions ==

= What does blogVault do that other backup services don’t? =

* Managed Offsite backups, so that your backups will remain safe even if your site goes down
* Manages your backup archive, stores up to 30 versions of your backup
* Automatically restores a specific backup when your site goes down
* Migrates your complete site to a new domain/server automatically
* Real-time backups
* Supports backup for large sites spanning many GBs
* E-mail/ Chat support to solve customer issues quickly

For a detailed comparison between blogVault and other popular backup plugins such as VaultPress, BackupBuddy, UpdraftPlus, myRepono and BackWPup, [click here](http://blogvault.net/comparison-between-popular-wordpress-backup-plugins/?bvsrc=wpplugin_readme).

The above are just a few points; we support everything that you can think of doing with your backup and then some more!

= Does blogVault support backup for Multi-Site installation? =
Yes. We do a complete backup of your multi-site WordPress installation.

= Will blogVault slow my site down? =
No. Moreover, we support incremental backup that will only pick up any changes files since the last backup. Further we do not do big dump of your database or zip large files during backup, limiting the load on your server.

= Does blogVault provide support to customers? =
Yes. We provide email as well as online chat support to resolve any backup or restore related issues.

= Do I have to backup all my data on my own? =
When you sign up with us, all your data is backed up automatically. After this, we have a scheduled backup every 24 hours. However, you have the option of initiating backup if required. We also support Real-time Backups of your site.

= How is backup data restored if my website crashes? =
We have an Auto-Restore option using which you can automatically restore all your backup data to the hosting server.

= Are your servers secure? =
We maintain multiple backups of the data on our extremely secure and robust servers as well as Amazon S3 servers.

= Can I upload the backup to my Dropbox account? =
Yes. You can store a snapshot of your backup to your Dropbox account.

== Changelog ==
= 1.08 =
* Changing the name to Backup Plugin by blogVault
* Updating the tested WordPress version to 3.9

= 1.06 =
* Setting blogVault key now validates the nonce to prevent XSRF
* Updating the plugin description with video introducing blogVault

= 1.05 =
* Real-time backup for WooCommerce

= 1.04 =
* Separating the different blogVault functions into classes
* Ability to update the blogVault Key
* Retrieving/Updating option only on the main site of a Network install

= 1.02 =
* Releasing the blogVault plugin into the WordPress repository.
