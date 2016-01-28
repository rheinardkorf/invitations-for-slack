=== Invitations for Slack ===
Contributors: rheinardkorf
Tags: slack, invitations, community, join, invites
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Z5WNWM5V86D4W
Requires at least: 4.4
Tested up to: 4.4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build a Slack community by allowing your visitors (or registered users) to invite themselves to your Slack team.

== Description ==
Invitations for Slack lets you use convenient shortcodes to show "Join us on Slack." buttons or Slack badges. Just add
your Slack token and use the shortcodes wherever you want your visitors to be able to invite themselves from.

### Features:

* Easy to use:
    * Visit <https://api.slack.com/web> to generate your Slack token.
    * Add the token to the plugin settings.
    * Use the [invitations_for_slack] or [invitations_for_slack_badge] shortcodes.
* Invitations are performed using the WP REST API which in turn communicates with the Slack API. No page reloads.

### Requirements:

* A Slack team and the team's access token.
* A self-hosted WordPress website (Not a WordPress.com website.)

== Installation ==

To get **Invitations for Slack** working, please follow these steps.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate "Invitations for Slack" through the 'Plugins' screen in WordPress
1. Once activated, click on the new "Invitations for Slack" menu item to configure your settings.
1. Make sure you get your Slack API token from <https://api.slack.com/web>

Now just use the [invitations_for_slack] and/or [invitations_for_slack_badge] shortcodes where you want your users to register from.

== Screenshots ==

1. Normal button version : [invitations_for_slack].
2. Badge version: [invitations_for_slack_badge].
3. Restricted to logged in users (as per settings).
4. Logged in user and alternate e-mail allowed OR unrestricted, anyone can register.
5. Already part of the team. Other errors have similar appearance.
6. Invitation sent successfully!
7. Plugin setup screen.

== Changelog ==

= 1.0.2 =
* Better handling of channel names to join.
* Reset link for invite form now shows and hides appropriately.

= 1.0.1 =
* Popup now shows above elements with overflow set to hidden.

= 1.0.0 =
* First public release.

= 0.1 =
* Initial plugin release.
