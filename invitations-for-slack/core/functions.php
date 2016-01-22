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
 * Class SlackInviter_Core_Functions
 *
 * Useful functions to use throughout the plugin
 */
class SlackInviter_Core_Functions {

	private static $data = array();

	/**
	 * Gets the team counters
	 *
	 * @return array
	 */
	public static function team_stats() {

		if ( ! isset( self::$data['team'] ) ) {
			self::retrieve_users();
		}

		$total  = isset( self::$data['team']['all'] ) ? count( self::$data['team']['all'] ) : 0;
		$active = isset( self::$data['team']['active'] ) ? count( self::$data['team']['active'] ) : 0;

		return array(
			'total'  => $total,
			'online' => $active
		);
	}

	/**
	 * Gets a list of users in the Slack team.
	 *
	 * ... and does some basic filtering: removing Slackbot, separating online from registered.
	 */
	public static function retrieve_users() {
		$token       = SlackInviter::get_setting( 'web_api_token', '' );
		$team_domain = SlackInviter::get_setting( 'team_domain', '' );
		$slack_url   = ! empty( $team_domain ) ? 'https://' . $team_domain . '.slack.com' : 'https://slack.com';

		$data = array(
			'token' => trim( $token ),
		);
		if ( ! empty( $token ) ) {
			$response = wp_remote_post( $slack_url . '/api/users.list?presence=1', array( 'body' => $data ) );
		} else {
			$response = new WP_Error();
		}

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$users = json_decode( wp_remote_retrieve_body( $response ) );
			if ( $users->ok ) {
				self::$data['team'] = array( 'all' => array(), 'active' => array() );

				foreach ( $users->members as $member ) {
					if ( 'USLACKBOT' != $member->id ) {
						self::$data['team']['all'][ $member->id ] = $member;
						if ( isset( $member->presence ) && 'active' == strtolower( $member->presence ) ) {
							self::$data['team']['active'][] = $member->id;
						}
					}
				}

			} else {
				self::$data['team'] = array();
			}
		}
	}

	/**
	 * Checks to see if user is already part of the Slack team
	 *
	 * @param int|bool $user_id
	 *
	 * @return bool
	 */
	public static function registered_with_slack( $user_id = false ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( false === $user_id ) {
			$user_id = get_current_user_id();
		}

		$user  = get_userdata( $user_id );
		$email = $user->user_email;

		if ( ! isset( self::$data['team'] ) ) {
			self::retrieve_users();
		}

		if( ! isset( self::$data['team'] ) ) {
			return false;
		}

		$is_member = false;
		foreach ( self::$data['team']['all'] as $member ) {
			if ( $is_member ) {
				return $is_member;
			}

			$is_member = $is_member || $email == $member->profile->email;
		}

		return $is_member;
	}

	/**
	 * Gets all the data we're going to need for the popup forms
	 *
	 * @return array|bool
	 */
	public static function prepare_invite_data() {

		$team                  = SlackInviter::get_setting( 'team_info', array() );
		$stats                 = SlackInviter_Core_Functions::team_stats();
		$only_logged_in        = SlackInviter::get_setting( 'only_logged_in', false );
		$allow_different_email = SlackInviter::get_setting( 'use_different_email', false );
		$logged_in_message     = SlackInviter::get_setting( 'logged_in_message',
			__( 'You need to be logged in to get your invite to join [TEAM_NAME] on Slack.', 'invitations-for-slack' )
		);

		$logged_in_message = str_replace( '[TEAM_NAME]', '<strong>' . $team->name . '</strong>', $logged_in_message );

		// Allow override of signup condition and message to display to user.
		// These are good hooks to use for other access control plugins.
		$user_allowed      = apply_filters( 'invitations-for-slack/user-allowed', true, get_current_user_id(), $team );
		$logged_in_message = apply_filters( 'invitations-for-slack/user-allowed-message', $logged_in_message, get_current_user_id(), $team );

		// Non-logged in users need to be able to specify an email address
		$allow_different_email = ! is_user_logged_in() && ! $only_logged_in ? true : $allow_different_email;

		if ( empty( $team ) ) {
			return false;
		}

		return array(
			'team'                  => $team,
			'stats'                 => $stats,
			'only_logged_in'        => $only_logged_in,
			'allow_different_email' => $allow_different_email,
			'logged_in_message'     => wp_kses( $logged_in_message, wp_kses_allowed_html( 'post' ) ),
			'user_allowed'          => $user_allowed,
			'is_logged_in'          => is_user_logged_in(),
			'is_registered'         => SlackInviter_Core_Functions::registered_with_slack( get_current_user_id() ),
			'user'                  => get_userdata( get_current_user_id() ),
		);

	}

	/**
	 * Creating a template with replaceable keywords
	 *
	 * Using a template because it will be required for JavaScript too.
	 *
	 * @param bool $data
	 *
	 * @return string
	 */
	public static function get_invite_box_template( $data = false ) {

		if ( false === $data ) {
			$data = self::prepare_invite_data();
		}

		$content = '';

		if ( ! $data['user_allowed'] || ( $data['only_logged_in'] && ! $data['is_logged_in'] ) ) {

			$content .= '    <div class="invite-box-wrapper hidden">' .
			            '        <div class="invite-box">' .
			            '             <div class="no-access-message">[LOGGED_IN_MESSAGE]</div>' .
			            '             <div class="team-stats">' . sprintf( __( '<strong class="online">%s</strong> users online. <strong class="registered">%s</strong> registered.', 'invitations-for-slack' ), '[STATS_ONLINE]', '[STATS_TOTAL]' ) . '</div>' .
			            '        </div>' .
			            '    </div>';

		} else {

			$content .= '    <div class="invite-box-wrapper hidden">' .
			            '        <div class="invite-box">';

			if ( ! $data['is_registered'] ) {
				$content .= '             <div class="tagline">' . sprintf( __( 'Join <strong>%s</strong> on Slack.', 'invitations-for-slack' ), '[TEAM_NAME]' ) . '</div>';
			} else {
				$content .= '             <div class="tagline">' . sprintf( __( 'Awesome! You\'re already chatting with<br/><strong>%s</strong>.', 'invitations-for-slack' ), '[TEAM_NAME]' ) . '</div>';
			}

			$content .= '             <div class="team-stats">' . sprintf( __( '<strong class="online">%s</strong> users online. <strong class="registered">%s</strong> registered.', 'invitations-for-slack' ), '[STATS_ONLINE]', '[STATS_TOTAL]' ) . '</div>';

			if ( $data['allow_different_email'] ) {
				$content .= '             <input type="text" placeholder="you@email.com" />';
			} else {
				$content .= '             <input type="hidden" value="[USER_EMAIL]" />';
			}

			if ( ! $data['is_registered'] ) {
				$content .= '             <button class="invite-button button" data-state="active">' . __( 'Yes. Please send my invite.', 'invitations-for-slack' ) . '</button><br/>';
			}

			if ( $data['allow_different_email'] ) {
				$content .= '             <a class="invite-box-reset hidden">' . __( 'Join with another e-mail address?', 'invitations-for-slack' ) . '</a>';
			}

			$content .= '        </div>' .
			            '    </div>';
		}


		return $content;

	}


}