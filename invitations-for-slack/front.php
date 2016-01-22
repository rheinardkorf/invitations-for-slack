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
 * Class SlackInviter_Front
 *
 * Initializes the plugin for handling the WP frontend.
 */
class SlackInviter_Front {

	public static function init() {

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		SlackInviter_Front_Shortcodes::init();
	}

	public static function enqueue_scripts() {

		wp_enqueue_style( 'invitations-for-slack', SlackInviter::$url . 'invitations-for-slack/assets/style.css' );
		wp_enqueue_script( 'invitations-for-slack', SlackInviter::$url . 'invitations-for-slack/scripts/script.js', array( 'jquery' ), SlackInviter::$version, false );
		wp_localize_script( 'invitations-for-slack', 'InvitationsForSlack', array(
			'endpoints' => array(
				'invite.send' => home_url( '/wp-json/' ) . SlackInviter_Core_Rest::$namespace_url . SlackInviter_Core_Rest::$routes['invite.send'],
				'team.stats' => home_url( '/wp-json/' ) . SlackInviter_Core_Rest::$namespace_url . SlackInviter_Core_Rest::$routes['team.stats']
			),
			'teamInfo' => SlackInviter::get_setting( 'team_info', '' ),
			'userIsMember' => SlackInviter_Core_Functions::registered_with_slack( get_current_user_id() ),
			'template' => SlackInviter_Core_Functions::get_invite_box_template(),
			'data' => SlackInviter_Core_Functions::prepare_invite_data(),
			'processing_text' => __( 'Processing...', 'invitations-for-slack' )
		) );

	}

}