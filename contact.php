<?php

//Source: https://github.com/SleeplessByte/php-nonces
class PHPNonce {

	const SALT = 'v(e%5Wh(auh2PNLs319@Jb^F$ujgGN!C';
	const EXPIRATION = 43200;
	const LENGTH = 10;
  	const CHECK_DEFINED = false;
  	const DEFINE = 'SomeDefine';

	/**
	 *	Creates a nonce (number used once)
	 *
	 *	Runs a hash algorithm on a name, session data and timestamp to generate
	 *	a one-time-numer. Saved as a session value
	 *
	 *	@param string $name the nonce name
	 *	@returns string the nonce
	 */
	public static function create( $name )  {

		if ( self::CHECK_DEFINED && !defined( self::DEFINE ) )
			die( 'go away' );

		if ( empty( $_SESSION[ 'nonces' ] ) )
			$_SESSION[ 'nonces' ] = array();

		$timestamp = time();

		$_SESSION[ 'nonces' ][ $name ] = array(
			'token' => PHPNonce::_create( $name, $timestamp ),
			'timestamp' => $timestamp
		);

		return $_SESSION[ 'nonces' ][ $name ][ 'token' ];
	}

	/**
	 *	Actually creates the nonce
	 *
	 *	@params string $name the name of the nonce
	 * 	@params int $timestamp the current UNIX timestamp
	 *	@returns string the nonce value
	 */
	protected static function _create( $name, $timestamp ) {
		return substr( md5( $name . self::SALT . session_id() . $timestamp ), 0, self::LENGTH );
	}

	/**
	 *	Verifies a nonce (number used once)
	 *
	 *	Simply reruns the algorithm to create the nonce and tries to match
	 *	it with the stored value. If it doesn't exist or doesn't match, returns
	 *	false. If it is expired, returns false. If it is valid, returns true;
	 *
	 *	@param string $token the received nonce value
	 *	@param string $name the nonce name
	 *	@returns boolean the valid state of the token
	 */
	public static function verify( $token, $name )  {

		if ( empty( $_SESSION[ 'nonces' ] ) )
			return false;
		if ( empty( $_SESSION[ 'nonces' ][ $name ] ) )
			return false;

		$token = $_SESSION[ 'nonces' ][ $name ][ 'token' ];
		$timestamp = $_SESSION[ 'nonces' ][ $name ][ 'timestamp' ];

		if ( $timestamp + self::EXPIRATION < time() ) {
			PHPNonce::remove( $name );
			return false;
		}

		return $token == self::_create( $name, $timestamp );
	}

	/**
	 *	Removes the nonce
	 */
	public static function remove( $name ) {

		if ( empty( $_SESSION[ 'nonces' ] ) )
			return false;
		if ( empty( $_SESSION[ 'nonces' ][ $name ] ) )
			return false;

		unset( $_SESSION[ 'nonces' ][ $name ] );
		return true;
	}
}


$name = $_POST["name"];
$email = $_POST["email"];
$phone = $_POST["phone"];
$question = $_POST["question"];

$content = "Query from All the Ducks contact form:\r\n\r\n";
$content .= "Name: {$name}\r\n";
$content .= "Email: {$email}\r\n";
$content .= "Phone: {$phone}\r\n";
$content .= "Question:\r\n{$question}";


require 'PHPMailer/PHPMailerAutoload.php';

$mail = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'email-smtp.us-west-2.amazonaws.com';  // Specify main and backup server
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'AKIAJBVVYFXRXNX4H4HA';                            // SMTP username
$mail->Password = 'Ak/0CG0K/EUsfm+B0qJg4WbPzK5C0Q+kH3aLC3FapokR';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

$mail->From = 'admin@alltheducks.com';
$mail->FromName = 'All the Ducks';
$mail->addAddress('shane@alltheducks.com', 'Shane Argo');  // Add a recipient
$mail->addAddress('wiley@alltheducks.com', 'Wiley Fuller');  // Add a recipient

$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
$mail->isHTML(false);                                  // Set email format to HTML

$mail->Subject = 'Query from All the Ducks contact form';
$mail->Body    = $content;

if(!$mail->send()) {
   $failpage = file_get_contents("contact-failure.html");
   $failpage = str_replace('!!!CONTENT!!!', htmlentities($question), $failpage);
   echo $failpage;
} else {
    include "contact-success.html";
}
?>