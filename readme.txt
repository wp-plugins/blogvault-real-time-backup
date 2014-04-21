=== blogVault Real-Time WordPress Backup ===
Contributors: akshatc, singhsivam
Tags: backup, backup plugin, backups, wordpress backups, wordpress backup, wordpress backup plugin, database backup, complete backup, wp backup, automatic backup, backup wordpress, theme backup
Donate link: http://blogvault.net
Requires at least: 1.5
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

blogVault is the best wordpress backup plugin that offers real-time backups. It makes it really easy restore, migrate, or secure your sites.

== Description ==

[blogVault](http://blogvault.net?bvsrc=wpplugin_readme) is a top-notch WordPress backup plugin that creates daily automatic backups of your WordPress site. Backup your entire site, including files and database, with this easy to use backup plugin.

**Top Features**:

1. **Daily automatic backup** for your entire site content, including Posts, Pages, Plugin, Images, Comments, etc.

2. Supports the 3 pillars that make a good wordpress backup plugin - backup, restore, and migration

3. Maintains **multiple copies** of backups on its own as well as Amazon S3 servers

4. **Real-time backup** so that no data is ever lost.

5. Upload any chosen version of your backup to your Dropbox account using the **Upload to Dropbox** feature

= Managed offsite backups =
Like in the case of any good wp backup plugin, blogVault supports both file and database backup so that your entire wordpress site is protected at all times. It creates offsite backups so that your content is safe even if your site goes down. It manages the backup archive and stores up to 30 wordpress backups at any point of times.

= Easy Restore =
If your site gets hacked, blogVault's easy auto-restore feature helps you get back on your feet within few minutes. It automatically restores a specific WordPress backup chosen by you onto the server.
blogVault also includes a test-restore feature using which you can test your changes before deploying them on the server or just verifying the integrity of a specific backup. The wp backup is temporarily restored on blogVault’s own servers for your validation.

= Migration =
blogVault’s simple migration feature helps you move to a new domain or a hosting provider seamlessly, **without any downtime**.

= Storage and Security =
blogVault provides the best security by maintaining multiple copies of backups on its own as well as Amazon S3 servers. This ensures redundancy of your wp backup.

You can also use the backup to Dropbox feature and upload any version of the backup to your Dropbox account. This feature enables you to store chosen versions of your WordPress backup beyond the 30 days for which blogVault stores it.

= Real-time Backup =
With a regular backup plugin, there is a possibility of losing data even if you have daily scheduled backups. A site with constant activity, such as e-commerce or news sites, undergoes multiple updates in a day. An outage in the middle of the day could bring heavy losses to such an organization. blogVault’s real-time backup ensures that any change to your site is immediately backed up. You no longer have to worry about losing a single transaction, even if your site crashes in between scheduled backups.
blogVault also has special handling for wooCommerce sites which regular backup plugins can’t handle.

= WordPress Multisite(WPMU) =

blogVault supports WordPress Multisite backups. The entire multi-site can be backed up, migrated or restored.

[vimeo http://vimeo.com/88638675]

= Support =
blogVault provides the best technical support in comparison to other wordpress backup plugins, using phone, email and chat. If you face any issues, the support team is always ready to help.

= Plans =
blogVault provides 3 different plans – basic, plus, and pro, to suit different backup needs. Whether you are a small time entrepreneur or a large company, blogVault puts an end to all your worries about losing data. blogVault includes a 7 day free trial so that you can explore all its features before committing to a plan. To learn more, visit our [pricing page](http://blogvault.net/pricing/?bvsrc=wpplugin_readme)

== Installation ==

= Automatic installation =

* The backup plugin can be installed automatically just like any other. To do an automatic install of blogVault, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.
* In the search field type **blogVault** and click Search Plugins. Once you’ve found our WordPress backup plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.
* After installation, click **Activate Plugin**. The last and final step is to register with us. Enter the fields in blogVault’s user form and click **Register**.

= Manual installation =

The backup plugin can also be installed manually by downloading it from the WordPress plugin repository and then uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation). Once completed, follow the same instructions as above to register with us and obtain a license key.


== Frequently Asked Questions ==

= What does blogVault do that other backup services don’t? =

* Offsite backup, so that your backups will remain safe even if your site goes down
* Manages your backup archive, stores up to 30 versions of your backup
* Automatically restores a specific backup when your site goes down
* Migrates your complete site to a new domain/server automatically
* Real-time backup for wooCommerce plugin
* Supports backup for large sites spanning many GBs
* E-mail/ Chat support to solve customer issues quickly

For a detailed comparison between blogVault and other popular backup plugins such as VaultPress, BackupBuddy, UpdraftPlus, myRepono and BackWPup, [click here](http://blogvault.net/comparison-between-popular-wordpress-backup-plugins/?bvsrc=wpplugin_readme).

The above are just a few points; we support everything that you can think of doing with your backup and then some more!

= Does blogVault support Multi-Site installation? =
Yes. We fully support WordPress Multi-Site installation.

= Will blogVault slow my site down? =
No. Moreover, we support incremental backup that will only pick up any changes files since the last version. Further we do not do big dump of your database or zip large files, limiting the load on your server.

= Does blogVault provide support to customers? =
Yes. We provide email as well as online chat support.

= Do I have to backup all my data on my own? =
When you sign up with us, all your data is backed up automatically. After this, we have a scheduled  backup every 24 hours. However, you have the option of initiating back up if required. We also support Real-time Backups of your site.

= How is data restored if my website crashes? =
We have an Auto-Restore option using which you can automatically restore all your data to the hosting server.

= Are your servers secure? =
We maintain multiple back up of the data on our extremely secure and robust servers as well as Amazon S3 servers.

= Is Upload to Dropbox supported? =
Yes. You can store a snapshot of your backup to your Dropbox account.

== Changelog ==
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
