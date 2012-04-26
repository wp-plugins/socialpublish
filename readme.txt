=== SocialPublish ===
Contributors: jorgenhorstink
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QC7KQUNMNTD8J
Tags: socialpublish, publish, post, twitter, facebook, social, media, platform
Author URI: http://socialpublish.io/
Plugin URI: http://wordpress.org/extend/plugins/socialpublish/
Requires at least: 2.5
Tested up to: 3.3.2
Stable tag: 1.1.1

SocialPublish is an easy to use service that automatically shares your blog post on Facebook and Twitter at the moment you publish it.

== Description ==

SocialPublish is an easy to use service that automatically shares your blog post on Facebook and Twitter at the moment you publish it. While other plugins rely on RSS feeds and usually have 30 minute delays, SocialPublish immediately shares your blog post. Because SocialPublish is a service, you can easily set up an account and connect your social media accounts. You don't have to do the hard process of registering Facebook and Twitter applications.

= How to set up =

Create a SocialPublish account:

1. Go to <http://socialpublish.io> and Sign Up for Free! (see: [screenshot 2](http://wordpress.org/extend/plugins/socialpublish/screenshots/))
2. Connect your social media accounts with a few simple clicks (see: [screenshot 3](http://wordpress.org/extend/plugins/socialpublish/screenshots/))
3. That's it, your SocialPublish account is now ready! (see: [screenshot 4](http://wordpress.org/extend/plugins/socialpublish/screenshots/))

Download the plugin and copy the socialpublish directory contents to the `/wp-content/plugins/socialpublish` directory.

Setup the SocialPublish WordPress Plugin:

1. Activate the plugin on the Plugins overview page
2. You get a message you have to provide the SocialPublish `access_token`
3. Click on the URL in the message, go to the SocialPublish settings via menu options `Settings > SocialPublish`
4. On the SocialPublish settings page, enter your SocialPublish `access_token`. You can find your access_token on your Dashboard on the SocialPublish website. (see: [screenshot 5](http://wordpress.org/extend/plugins/socialpublish/screenshots/))

That's all! You're ready to go now! If the `access_token` is valid, you'll see an overview of the social media accounts you have connected to your SocialPublish account earlier (see: [screenshot 6](http://wordpress.org/extend/plugins/socialpublish/screenshots/)).

= How to use the SocialPublish WordPress plugin =

1. Create a new WordPress Post
2. On the editing screen, click on the right top on Screen Options, and enable the SocialPublish panel
3. You're able now to decide to which social media accounts you want to share the Blog Post (see: [screenshot 1](http://wordpress.org/extend/plugins/socialpublish/screenshots/)). Optional you can provide a custom message (otherwise the Blog Title will be used), and hashtags for Twitter.

== Screenshots ==

1. Screenshot 1 - When you write a Blog Post and have the SocialPublish Screen Option panel enabled, you're able to provide optional hashtags and a message text you want to provide within your shared Twitter and/or Facebook post. You can also decide to which social media accounts you want to share your Blog Post.
2. Screenshot 2 - Go to <http://socialpublish.io> and Sign Up for Free!
3. Screenshot 3 - Connect your social media accounts with a few simple clicks
4. Screenshot 4 - That's it, your SocialPublish account is now ready!
5. Screenshot 5 - On the SocialPublish Settings page, enter your SocialPublish `access_token`. You can find your access_token on your Dashboard on the SocialPublish website.
6. Screenshot 6 - If the `access_token` is valid, you'll see an overview of the social media accounts you have connected to your SocialPublish account earlier.

== Changelog ==

= 1.1.1 =
* Minor backwards compatibility fixes on editing an old post
* Prevent PHP warning if fsockopen cannot connect to the server

= 1.1 =
* Made important changes to the SocialPublish API (current version = 0.2).
* The release also includes a fix for the Facebook Page problem.

= 1.0.2 =
* Increased the fsockopen time out to 10 seconds, to prevent early time outs 

= 1.0.1 =
* Minor bug fixes 

= 1.0.0 =
* Fixed a bug caused by allow_url_fopen = false on some configuration. If you've had problems with an invalid access_token, this will probably fix it!

= 0.0.1 =
* The first test release.
* Updated the readme.txt 