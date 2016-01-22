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
 * Class SlackInviter_Admin_Menu
 *
 * Taps into the WordPress menu system (Dashboard) to add Settings Page
 */
class SlackInviter_Admin_Menu {

	public static function init() {

		// Menus
		add_action( 'admin_menu', array( __CLASS__, 'menu_pages' ) );

		// Init the settings page
		SlackInviter_Admin_Settings::init();
	}

	public static function menu_pages() {

		// Add primary menu page
		$admin_slug = 'invitations-for-slack';

		add_menu_page( __( 'Invitations for Slack', 'invitations-for-slack' ),
			__( 'Invitations<br/>for Slack', 'invitations-for-slack' ),
			'manage_options',
			$admin_slug,
			array( 'SlackInviter_Admin_Settings', 'render' ),
			SlackInviter::$url . 'invitations-for-slack/assets/icon.svg'
		);

		do_action( 'invitations-for-slack/admin_page', $admin_slug );
	}

}