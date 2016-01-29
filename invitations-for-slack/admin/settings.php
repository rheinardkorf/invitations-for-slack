<?php

/**
 * @copyright 2013-2014 Rheinard Korf
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */

/**
 * Class SlackInviter_Admin_Settings
 *
 * The Settings page and everything else that goes with it.
 */
class SlackInviter_Admin_Settings {


	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'process_settings' ) );
	}

	/**
	 * Shows the settings page
	 */
	public static function render() {

		$title       = __( 'Invitations for Slack', 'invitations-for-slack' );
		$icon_black  = SlackInviter::$url . 'invitations-for-slack/assets/icon_black.svg';
		$token       = SlackInviter::get_setting( 'web_api_token', '' );
		$team_domain = SlackInviter::get_setting( 'team_domain', '' );

		$slack_url = ! empty( $team_domain ) ? 'https://' . $team_domain . '.slack.com' : 'https://slack.com';
		$data      = array(
			'token' => trim( $token ),
		);

		if ( ! empty( $token ) ) {
			$response = wp_remote_post( $slack_url . '/api/team.info', array( 'body' => $data ) );
		} else {
			$response = new WP_Error();
		}
		$team = '';
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$team = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! isset( $team->error ) ) {
				$team = $team->team;
				SlackInviter::set_setting( 'team_domain', $team->domain );
				SlackInviter::set_setting( 'team_info', $team );
			} else {
				$team = array();
				SlackInviter::set_setting( 'team_domain', '' );
				SlackInviter::set_setting( 'team_info', '' );
			}
		}


		?>
		<div class="wrap">

			<div id="icon-themes" class="icon32"></div>
			<h2><img src="<?php echo esc_attr( $icon_black ); ?>"
			         style="width:20px; height: 20px; margin-bottom: -2px;"/> <?php echo esc_html( $title ); ?></h2>
			<hr/>
			<?php settings_errors(); ?>
			<form method="post">
				<?php wp_nonce_field( 'update_invitations_for_slack', 'invitations_for_slack_nonce' ); ?>
				<table class="form-table">
					<tbody>

					<!-- WEB API SETTINGS -->
					<tr>
						<?php $title = __( 'Web API Token', 'invitations-for-slack' ); ?>
						<th scope="row"><?php echo esc_html( $title ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo esc_html( $title ); ?></span>
								</legend>

								<label class="description" for="slackinviter_options[web_api_token]"
								       style="min-width: 415px;">
									<input type="text" id="slackinviter_options[web_api_token]"
									       name="slackinviter_options[web_api_token]"
									       style="width: 100%;"
									       placeholder="<?php esc_attr_e( 'Enter your Web API token here', 'invitations-for-slack' ); ?>"
									       value="<?php echo esc_attr( $token ); ?>"/>
								</label><br/>

								<p class="description">
									<?php
									echo sprintf( __( 'You can generate you WEB API token at %s. Note: You will need to be an administrator of the team.', 'invitations-for-slack' ), '<a href="https://api.slack.com/web">https://api.slack.com/web</a>' );
									?>
								</p>
							</fieldset>
						</td>
					</tr>

					<!-- WEB API SETTINGS -->
					<?php
					$channels = SlackInviter::get_setting( 'channels', '' );
					$channels = implode( "\n", $channels );
					?>
					<tr>
						<?php $title = __( 'Channels to join', 'invitations-for-slack' ); ?>
						<th scope="row"><?php echo esc_html( $title ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo esc_html( $title ); ?></span>
								</legend>

								<textarea id="slackinviter_options[channels]"
								          name="slackinviter_options[channels]"
								          style="min-width: 320px; min-height:140px;"><?php echo esc_html( $channels ); ?></textarea>

								<p class="description">
									<?php esc_html_e( 'Enter the names of channels to automatically join. One channel per line.', 'invitations-for-slack' ); ?>
								</p>
							</fieldset>
						</td>
					</tr>

					<!-- SHORTCODES -->
					<tr>
						<?php $title = __( 'Shortcodes', 'invitations-for-slack' ); ?>
						<th scope="row"><?php echo esc_html( $title ); ?></th>
						<td>
							<p class="description"><?php esc_html_e( 'The following shortcodes are required to create the the Slack invitation forms. Use them on your invitation page(s).', 'invitations-for-slack' ); ?></p>
							<dl>
								<dt><code>[invitations_for_slack]</code></dt>
								<dd><?php esc_html_e( 'The primary code to allow users to sign up', 'invitations-for-slack' ); ?></dd>
								<dt><code>[invitations_for_slack_badge clickable="yes|no"]</code></dt>
								<dd><?php esc_html_e( 'Shows a smaller Slack badge. Clickable is "yes" by default. Specifying "no" will just show a static badge.', 'invitations-for-slack' ); ?></dd>
							</dl>
						</td>
					</tr>

					<!-- LOGGED IN USERS -->
					<?php
					$logged_in_message = SlackInviter::get_setting( 'logged_in_message',
						__( 'You need to be logged in to get your invite to join [TEAM_NAME] on Slack.', 'invitations-for-slack' )
					);
					?>
					<tr>
						<?php $title = __( 'Logged in users', 'invitations-for-slack' ); ?>
						<th scope="row"><?php echo esc_html( $title ); ?></th>
						<td>
							<label class="description" for="slackinviter_options[only_logged_in]">
								<input type="checkbox" id="slackinviter_options[only_logged_in]"
								       name="slackinviter_options[only_logged_in]"
								       value="1" <?php checked( '1', SlackInviter::get_setting( 'only_logged_in', false ) ); ?> />
								<?php _e( 'Only logged in users can get invites.', SlackInviter::$td ); ?>
							</label><br/>
							<label class="description" for="slackinviter_options[use_different_email]">
								<input type="checkbox" id="slackinviter_options[use_different_email]"
								       name="slackinviter_options[use_different_email]"
								       value="1" <?php checked( '1', SlackInviter::get_setting( 'use_different_email', false ) ); ?> />
								<?php _e( 'Allow e-mail different to user account e-mail. <small>( Not recommended )</small>', SlackInviter::$td ); ?>
							</label><br/>
							<p>
								<strong><?php esc_html_e( 'Message for visitors (logged out users).', 'invitations-for-slack' ); ?></strong>
							</p>
							<textarea id="slackinviter_options[logged_in_message]"
							          name="slackinviter_options[logged_in_message]"
							          style="min-width: 320px; min-height:140px;"><?php echo esc_html( $logged_in_message ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Type in a message to display to logged out users. Keep it brief!', 'invitations-for-slack' ); ?></p>
							<p class="description"><?php esc_html_e( 'Use [TEAM_NAME] as a placeholder for your team\'s name.', 'invitations-for-slack' ); ?></p>
						</td>
					</tr>

					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>

		</div>


		<?php
	}


	/**
	 * Processes any settings changes
	 */
	public static function process_settings() {

		// Invitations for Slack Settings Processing
		if ( ! empty( $_REQUEST ) && ! empty( $_REQUEST['invitations_for_slack_nonce'] ) && check_admin_referer( 'update_invitations_for_slack', 'invitations_for_slack_nonce' ) ) {

			$post_options = $_POST['slackinviter_options'];


			$post_options["web_api_token"] = isset( $post_options["web_api_token"] ) ? sanitize_text_field( $post_options["web_api_token"] ) : '';

			$post_options["channels"] = preg_replace( '/(\\r\\n)|\\n/', ',', $post_options["channels"] );
			$post_options["channels"] = isset( $post_options["channels"] ) ? sanitize_text_field( $post_options["channels"] ) : '';
			$post_options["channels"] = explode( ",", $post_options["channels"] );

			$post_options["logged_in_message"] = sanitize_text_field( $post_options["logged_in_message"] );

			if ( ! isset( $post_options['only_logged_in'] ) ) {
				$post_options['only_logged_in'] = false;
			}
			if ( ! isset( $post_options['use_different_email'] ) ) {
				$post_options['use_different_email'] = false;
			}

			$settings = get_option( 'invitations_for_slack' );
			$settings = empty( $settings ) ? array() : $settings;

			$options = array_merge( $settings, $post_options );

			update_option( 'invitations_for_slack', $options );

			add_settings_error(
				'invitations_for_slack',
				esc_attr( 'invitations-for-slack-updated' ),
				__( 'Invitations for Slack updated.', 'invitations-for-slack' ),
				'updated'
			);
		}

	}


}
