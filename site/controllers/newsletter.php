<?php

return function ( $site, $page, $kirby ) {
	$session = $kirby->session();
	$notice = null;

	if( $session->get( 'notice' ) ) {
		$notice = $session->get( 'notice' );
		$session->remove( 'notice' );
	}

	return ['notice' => $notice];
};
