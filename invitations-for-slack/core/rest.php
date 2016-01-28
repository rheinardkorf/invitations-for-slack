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
 * Class SlackInviter_Core_Rest
 *
 * Registers a few REST API routes for AJAX magic.
 */
class SlackInviter_Core_Rest {

	public static $namespace = 'invitations-for-slack';
	public static $version = '1';
	public static $namespace_url;

	public static $routes = array();

	public static function init() {

		self::$namespace_url = self::$namespace . '/v' . self::$version;

		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );

		/**
		 * Specify routes in an array. Useful later.
		 */
		self::$routes['team.stats']  = '/team.stats';
		self::$routes['invite.send'] = '/invite.send';
		self::$routes['team.badge']  = '/team.badge';

	}

	public static function register_routes() {

		/**
		 * Gets team counters
		 */
		register_rest_route( self::$namespace_url, self::$routes['team.stats'],
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( __CLASS__, 'team_stats' ),
			)
		);

		/**
		 * Posts invite request via Slack API
		 */
		register_rest_route( self::$namespace_url, self::$routes['invite.send'],
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( __CLASS__, 'invite_send' ),
			)
		);

	}


	/**
	 * Gets team counters
	 */
	public static function team_stats( WP_REST_Request $request ) {
		return SlackInviter_Core_Functions::team_stats();
	}

	/**
	 * Posts invite request via Slack API
	 */
	public static function invite_send( WP_REST_Request $request ) {
		$params = $request->get_params();

		$token       = SlackInviter::get_setting( 'web_api_token', '' );
		$team_domain = SlackInviter::get_setting( 'team_domain', '' );

		$slack_url   = ! empty( $team_domain ) ? 'https://' . $team_domain . '.slack.com' : 'https://slack.com';

		$data = array(
			'token'      => trim( $token ),
		);

		// Get Channels
		if ( ! empty( $token ) ) {
			$response = wp_remote_post( $slack_url . '/api/channels.list?t=1', array( 'body' => $data ) );
		} else {
			$response = new WP_Error();
		}

		$message = __( 'Oops. Can\'t talk to Slack right now.', 'invitations-for-slack' );
		$status  = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$channels_list = json_decode( wp_remote_retrieve_body( $response ) );
			if ( $channels_list->ok ) {

				$data = array(
					'email'      => sanitize_email( $params['ifs_email'] ),
					'channels'   => self::get_channels_for_invite( self::map_channels( $channels_list->channels ) ),
					'first_name' => '',
					'token'      => trim( $token ),
					'set_active' => 'true',
					'_attempts'  => '1',
				);

				// Attempt Invite
				if ( ! empty( $token ) ) {
					$response = wp_remote_post( $slack_url . '/api/users.admin.invite?t=1', array( 'body' => $data ) );
				} else {
					$response = new WP_Error();
				}

				$reason = '';

				if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
					$invite = json_decode( wp_remote_retrieve_body( $response ) );
					if ( $invite->ok ) {
						$message = __( 'You\'re Invited! Check your inbox.', 'invitations-for-slack' );
						$status  = true;
					}
					if ( isset( $invite->error ) ) {
						switch ( $invite->error ) {
							case 'already_in_team' :
								$message = __( 'Already part of the team!', 'invitations-for-slack' );
								break;
							case 'already_invited' :
							case 'sent_recently' :
								$message = __( 'Already sent. Check your inbox.', 'invitations-for-slack' );
								break;
							case 'invalid_email' :
								$message = __( 'Oops. Invalid e-mail address.', 'invitations-for-slack' );
								break;
							default:
								$message = sprintf( __( '"%s". Let us know!', 'invitations-for-slack' ), $invite->error );
								break;
						}
						$status = false;
						$reason = $invite->error;
					}
				}
			}
		}

		return array( 'invite_successful' => $status, 'message' => $message, 'reason' => $reason );
	}

	public static function map_channels( $channels_list ) {

		$channels = array( 'archived' => array(), 'active' => array() );

		foreach( $channels_list as $channel ) {
			if( ! $channel->is_archived ) {
				$channels[ 'active' ][ $channel->id ] = $channel->name;
			} else {
				$channels[ 'archived' ][ $channel->id ] = $channel->name;
			}
		}

		return $channels;
	}

	public static function get_channels_for_invite( $channels ) {
		$expected_channels = SlackInviter::get_setting( 'channels', '' );

		$channel_ids = array();
		foreach( $channels['active'] as $id => $name ) {
			if( in_array( $name, $expected_channels ) ) {
				$channel_ids[] = $id;
			}
		}

		return implode( ",", $channel_ids );
	}

}