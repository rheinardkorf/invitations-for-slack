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
 * Class SlackInviter_Core
 *
 * Initializes relevant classes and creates a few hooks.
 */
class SlackInviter_Core {

	public static function init() {

		if( is_admin() ) {
			// Init the Admin class
			SlackInviter_Admin::init();
			do_action( 'invitations-for-slack/admin_loaded' );
		} else {
			// Init the Front class
			SlackInviter_Front::init();
			do_action( 'invitations-for-slack/front_loaded' );
		}

		SlackInviter_Core_Rest::init();

		// Very clear line between backend and frontend above,
		// this action will fire regardless of front or back.
		do_action( 'invitations-for-slack/loaded' );
	}

	public static function activated() {
		do_action( 'invitations-for-slack/activated' );
	}

	public static function deactivated() {
		do_action( 'invitations-for-slack/deactivated' );
	}


}