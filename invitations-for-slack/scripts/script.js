/**
 * InvitationsForSlack
 */

var InvitationsForSlack = InvitationsForSlack || [];

/**
 * Invitations For Slack Data Components
 */

InvitationsForSlack.teamStats = {total: 0, online: 0};

InvitationsForSlack.vars = {};
InvitationsForSlack.handler = {};

InvitationsForSlack.sendInvite = function ( email, callback ) {
	var params = 'ifs_email=' + encodeURIComponent( email );
	var url = InvitationsForSlack.endpoints['invite.send'];

	var XHR = new XMLHttpRequest();
	XHR.onreadystatechange = function () {
		if ( XHR.readyState == 4 && XHR.status == 200 ) {
			callback( XHR.responseText );
		}
	}
	XHR.open( "POST", url, true ); // true for asynchronous
	XHR.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
	XHR.send( params );
};


InvitationsForSlack.getTeamStats = function ( callback ) {
	var url = InvitationsForSlack.endpoints['team.stats'];
	var XHR = new XMLHttpRequest();
	XHR.onreadystatechange = function () {
		if ( XHR.readyState == 4 && XHR.status == 200 ) {

			InvitationsForSlack.teamStats = JSON.parse( XHR.responseText );

			if ( typeof callback !== 'undefined' ) {
				callback( XHR.responseText );
			}

		}
	}
	XHR.open( "GET", url, true );
	XHR.send();
};

InvitationsForSlack.templateReplace = function () {
	var content = InvitationsForSlack.template;
	var data = InvitationsForSlack.data;
	var online = InvitationsForSlack.teamStats.online;
	var total = InvitationsForSlack.teamStats.total;

	if ( 0 == online ) {
		online = data.stats.online;
	}
	if ( 0 == total ) {
		total = data.stats.total;
	}

	content = content.replace( '[LOGGED_IN_MESSAGE]', data.logged_in_message );
	content = content.replace( '[STATS_ONLINE]', online );
	content = content.replace( '[STATS_TOTAL]', total );
	content = content.replace( '[TEAM_NAME]', InvitationsForSlack.teamInfo.name );
	if ( InvitationsForSlack.data.user ) {
		content = content.replace( '[USER_EMAIL]', InvitationsForSlack.data.user.data.user_email );
	}

	return content;
}

InvitationsForSlack.handler.invite_button = function ( e ) {

	var $ = jQuery;

	var parent = $( this ).parents( '.invitations-for-slack-wrapper' );
	if ( parent.length < 1 ) {
		parent = $( this ).parents( '.slackbadge_container' );
	}
	InvitationsForSlack.vars.lastParent = parent;

	var button = this;
	var email_box = $( button ).siblings( 'input' )[0];
	var email_address = $( email_box ).val();
	var state = $( button ).attr( 'data-state' );

	// Close the popup if nothing else needs doing
	if ( state === 'active' ) {
		$( button ).attr( 'data-state', 'processing' );
	} else if ( state === 'processed' ) {
		$( InvitationsForSlack.vars.lastParent ).find( '.invite-box-wrapper' ).addClass( 'hidden' );
		return;
	} else {
		return;
	}

	$( button )[0].innerText = InvitationsForSlack.processing_text;

	InvitationsForSlack.sendInvite( email_address, function ( response ) {
		response = JSON.parse( response );

		// Freeze button
		$( button ).attr( 'data-state', 'processed' );

		// Update button text and hide the input field
		$( button )[0].innerText = response.message;
		$( button ).siblings( 'input' ).addClass( 'hidden' );
		$( InvitationsForSlack.vars.lastParent ).find( '.invite-box-reset' ).removeClass( 'hidden' );

		if ( response.invite_successful ) {
			$( button ).addClass( 'ok' );
		} else {
			$( button ).addClass( 'error' );
		}

	} );

}

InvitationsForSlack.handler.invite_reset = function ( e ) {

	var $ = jQuery;

	e.preventDefault();
	e.stopImmediatePropagation();

	var button = $( InvitationsForSlack.vars.lastParent ).find( '.button.invite-button' );

	$( button ).attr( 'data-state', 'active' );
	$( button ).removeClass( 'error' );
	$( button ).removeClass( 'ok' );
	$( button )[0].innerText = InvitationsForSlack.vars.originalInviteButtonText;
	$( button ).siblings( 'input' ).removeClass( 'hidden' );
	$( button ).siblings( 'input' ).val( '' );
	$( InvitationsForSlack.vars.lastParent ).find( '.invite-box-reset' ).addClass( 'hidden' );

}

/**
 * Invitations For Slack UI Components
 */

InvitationsForSlack.UI = InvitationsForSlack.UI || {};


/**
 * Invitations For Slack Page Load
 */

(
	function ( $ ) {


		$( document ).ready( function ( $ ) {

			/**
			 * Get initial team stats
			 */
			InvitationsForSlack.getTeamStats();

			/**
			 * Toggle Join Button/Badge
			 */
			$( '.invitations-for-slack-wrapper .button.join-button, #ifs_slackbadge' ).on( 'click', function ( e ) {

				var invite_box = {};

				if ( $( this ).siblings( '.invite-box-wrapper' ).length > 0 ) {
					invite_box = $( this ).siblings( '.invite-box-wrapper' )[0];
				} else {
					var parent = $( this ).parents( '.invitations-for-slack-wrapper' );
					if ( parent.length < 1 ) {
						parent = $( this ).parents( '.slackbadge_container' );
					}
					InvitationsForSlack.vars.lastParent = parent;

					if ( $( parent ).hasClass( 'dont-click' ) ) {
						return;
					}

					if ( InvitationsForSlack.vars.lastParent.length > 0 ) {
						$( InvitationsForSlack.vars.lastParent ).append( InvitationsForSlack.templateReplace() );
						$( InvitationsForSlack.vars.lastParent ).find( '.button.invite-button' ).on( 'click', InvitationsForSlack.handler.invite_button );
						$( InvitationsForSlack.vars.lastParent ).find( '.invite-box-reset' ).on( 'click', InvitationsForSlack.handler.invite_reset );
						invite_box = $( this ).siblings( '.invite-box-wrapper' )[0];
						var invite_button = $( InvitationsForSlack.vars.lastParent ).find( '.invite-button' );
						if ( invite_button.length > 0 ) {
							InvitationsForSlack.vars.originalInviteButtonText = $( invite_button )[0].innerHTML;
						}
					}
				}

				// Toggle
				if ( $( invite_box ).hasClass( 'hidden' ) ) {
					$( invite_box ).removeClass( 'hidden' );
				} else {
					$( invite_box ).addClass( 'hidden' );
				}

			} );


			/**
			 * Invite Button Clicked
			 */
			$( InvitationsForSlack.vars.lastParent ).find( '.button.invite-button' ).on( 'click', InvitationsForSlack.handler.invite_button );

			/**
			 * Reset Invite Box
			 */
			$( InvitationsForSlack.vars.lastParent ).find( '.invite-box-reset' ).on( 'click', InvitationsForSlack.handler.invite_reset );


		} );


	}
)( jQuery );