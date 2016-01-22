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
 * Class SlackInviter_Front_Shortcodes
 *
 * Creates plugin shortcodes
 */
class SlackInviter_Front_Shortcodes {

	public static function init() {

		// Register Shortcodes
		add_shortcode( 'invitations_for_slack', array( __CLASS__, 'invitations_for_slack' ) );
		add_shortcode( 'invitations_for_slack_badge', array( __CLASS__, 'invitations_for_slack_badge' ) );
	}

	/**
	 * Creates the Slack join button
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function invitations_for_slack( $args ) {

		$data = SlackInviter_Core_Functions::prepare_invite_data();

		if ( empty( $data ) || empty( $data['team'] ) ) {
			return '';
		}

		$content = '<div class="invitations-for-slack-wrapper">' .
		           '    <button class="join-button button">' . __( 'Join us on Slack!', 'invitations-for-slack' ) . '</button>' .
		           '    <div class="team-numbers">' .
		           '        ' . sprintf( __( '%d / %d', 'invitations-for-slack' ), $data['stats']['online'], $data['stats']['total'] ) .
		           '    </div>' .
		           '</div>';

		return $content;
	}


	/**
	 * Creates the Slack badge
	 *
	 * Use `clickable="no"` to make it a static badge
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function invitations_for_slack_badge( $args ) {

		$args = shortcode_atts( array(
			'clickable' => 'yes',
		), $args, 'bartag' );

		$args['clickable'] = sanitize_text_field( $args['clickable'] );
		$clickable_class = 'yes' === strtolower( $args['clickable'] ) ? '' : 'dont-click';

		$stats  = SlackInviter_Core_Functions::team_stats();
		$output = $stats['online'] . ' / ' . $stats['total'];
		$length = 70 + ( ( strlen( $output ) - 1 ) * 6 );

		$content = '<div class="slackbadge_container ' . $clickable_class . '">
		<svg id="ifs_slackbadge" xmlns="http://www.w3.org/2000/svg" width="' . $length . '" height="20" style="shape-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd">
			<mask id="slackbadge_left_mask">
				<rect x="0" y="0" width="52" height="20" rx="3" fill="#ffffff"/>
				<rect x="10" y="0" width="42" height="20" fill="#ffffff"/>
			</mask>
			<mask id="slackbadge_right_mask">
				<rect x="20" y="0" width="' . ( $length - 20 ) . '" height="20" rx="3" fill="#ffffff"/>
				<rect x="20" y="0" width="10" height="20" fill="#ffffff"/>
			</mask>
			<rect id="slackbadge_right" width="' . $length . '" height="20" rx="3" style="mask: url(#slackbadge_right_mask);" />
			<rect id="slackbadge_left" width="' . $length . '" height="20" rx="3" style="mask: url(#slackbadge_left_mask);" />
			<g id="slackbadge_logo" transform="matrix(0.7,0,0,0.7,3,3)">
				<path id="slack_cross" d="M20,12.5c0-2.5-0.5-4.3-1.1-6.1c-0.5-1.5-1.1-2.9-2.1-4.2c-1.3-1.6-3-2.3-5-2.2
					C10.4,0,9,0.3,7.7,0.7C6,1.2,4.2,1.8,2.7,2.8C1.2,3.9,0.2,5.3,0,7.2c-0.1,1.3,0,2.6,0.3,3.9c0.5,2.1,1.1,4.1,2.3,6
					c1.2,2,3,3,5.4,2.9c1.2,0,2.4-0.3,3.6-0.5c1.9-0.4,3.8-1.1,5.4-2.1C19.1,16.1,20,14.3,20,12.5z M15.6,11.7
					c-0.2,0.1-0.4,0.1-0.6,0.2c-0.2,0.1-0.4,0.1-0.6,0.2c0.1,0.4,0.3,0.8,0.4,1.3c0.2,0.6-0.1,1.2-0.6,1.4c-0.6,0.2-1.1-0.1-1.3-0.7
					c-0.2-0.4-0.3-0.8-0.4-1.3c-0.9,0.3-1.8,0.6-2.7,0.9c0.1,0.4,0.3,0.8,0.4,1.2c0.2,0.7,0,1.2-0.6,1.4c-0.6,0.2-1.1-0.1-1.4-0.8
					c-0.1-0.4-0.3-0.8-0.4-1.1c0,0,0,0-0.1-0.1c-0.4,0.1-0.8,0.3-1.2,0.4c-0.6,0.2-1.2-0.1-1.3-0.7C5,13.6,5.3,13,5.9,12.8
					c0.4-0.1,0.8-0.3,1.3-0.4c-0.3-0.9-0.6-1.7-0.9-2.6C5.8,10,5.4,10.1,5,10.2c-0.6,0.2-1.2-0.1-1.4-0.7C3.5,9,3.8,8.5,4.4,8.3
					C4.8,8.1,5.2,8,5.6,7.9C5.4,7.4,5.3,7,5.2,6.5C5,5.9,5.2,5.4,5.8,5.2C6.3,5,6.9,5.3,7.1,5.9c0.2,0.4,0.3,0.9,0.5,1.3
					c0.9-0.3,1.7-0.6,2.7-0.9c-0.2-0.5-0.3-0.9-0.5-1.4c-0.2-0.5,0.2-1.1,0.7-1.2c0.5-0.2,1.1,0.1,1.3,0.5c0.1,0.2,0.1,0.4,0.2,0.6
					C12,5,12.1,5.3,12.2,5.6c0.5-0.1,0.9-0.3,1.3-0.4c0.6-0.2,1.1,0.1,1.3,0.6c0.2,0.5-0.1,1.1-0.6,1.3c-0.4,0.2-0.9,0.3-1.3,0.5
					c0.3,0.9,0.6,1.7,0.9,2.6c0.4-0.1,0.8-0.3,1.2-0.4c0.6-0.2,1.2,0,1.4,0.6C16.5,11,16.2,11.5,15.6,11.7z"/>
				<path id="slack_cross_center" d="M8.2,9.2c0.3,0.9,0.6,1.7,0.9,2.6c0.9-0.3,1.8-0.6,2.7-0.9c-0.3-0.9-0.6-1.7-0.9-2.6
					C10,8.6,9.1,8.9,8.2,9.2z"/>
			</g>
			<g>
				<text id="slackbadge_slack_text" x="20" y="14">slack</text>
				<text id="slackbadge_stats_text" x="60" y="14">' . $output . '</text>
			</g>
		</svg>
		</div>';

		return $content;

	}

}