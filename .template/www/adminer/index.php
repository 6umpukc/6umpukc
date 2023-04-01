<?php

// see example in "C:\Program Files (x86)\Local\resources\extraResources\adminer" ( localwp.com )

header( 'Cache-Control: no-cache' );

function delete_cookie( $name ) {
	setcookie( $name, '', time() - 3600 );

	if ( isset( $_COOKIE[ $name ] ) ) {
		unset( $_COOKIE[ $name ] );
	}
}

if ( isset( $_POST['logout'] ) ) {
	delete_cookie( 'local_adminer_session' );
	delete_cookie( 'adminer_sid' );
	delete_cookie( 'adminer_permanent' );
	delete_cookie( 'adminer_version' );
}

if ( ! empty( $_COOKIE['local_adminer_session'] ) ) {
	$session_id = $_COOKIE['local_adminer_session'];

	if ( $session_id != getenv( 'ADMINER_SESSION_TOKEN' ) ) {
		delete_cookie( 'adminer_sid' );
		delete_cookie( 'adminer_permanent' );
		delete_cookie( 'adminer_version' );
	}
}

if ( ! $_COOKIE['adminer_sid'] ) {
	$_POST['auth'] = array(
		'driver'    => 'server',
		'server'    => '',
		'username'  => $_GET[ 'username' ],
		'password'  => $_GET[ 'password' ],
		'db'        => $_GET[ 'db' ],
		'permanent' => 'localhost',
	);

	$_SESSION['token'] = rand( 1, 1e6 );

	setcookie( 'local_adminer_session', getenv( 'ADMINER_SESSION_TOKEN' ) );
}

require_once( 'adminer.php' );
