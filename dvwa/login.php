<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'phpids' ) );

dvwaDatabaseConnect();

if( isset( $_POST[ 'Login' ] ) ) {
	// Anti-CSRF
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'login.php' );

	$user = $_POST[ 'username' ];
	$user = stripslashes( $user );
	$user = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $user ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

	$pass = $_POST[ 'password' ];
	$pass = stripslashes( $pass );
	$pass = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
	$pass = md5( $pass );

	$query = ("SELECT table_schema, table_name, create_time
				FROM information_schema.tables
				WHERE table_schema='{$_DVWA['db_database']}' AND table_name='users'
				LIMIT 1");
	$result = @mysqli_query($GLOBALS["___mysqli_ston"],  $query );
	if( mysqli_num_rows( $result ) != 1 ) {	
		
		// Create table 'users'
		if(!@((bool)mysqli_query($GLOBALS["___mysqli_ston"],"USE ".$_DVWA[ 'db_database' ])) ) {
			//dvwaMessagePush( 'Could not connect to database.' );
			//dvwaPageReload();
		}
		
		$create_tb = "CREATE TABLE users (user_id int(6),first_name varchar(15),last_name varchar(15),user varchar(15),password varchar(32),avatar varchar(70),last_login TIMESTAMP,failed_login INT(3),PRIMARY KEY (user_id));";		
		if(!mysqli_query($GLOBALS["___mysqli_ston"],$create_tb)){		
			//dvwaMessagePush( "Table could not be created<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : 		(($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
			//dvwaPageReload();
		}
		//dvwaMessagePush( "'users' table was created." );
		
		// Insert some data into users
		$avatarUrl = '/hackable/users/';
		
		$insert = "INSERT INTO users VALUES		
			('1','admin','admin','admin',MD5('password'),'{$avatarUrl}admin.jpg',NOW(),'0'),		
			('2','Gordon','Brown','gordonb',MD5('abc123'),'{$avatarUrl}gordonb.jpg',NOW(),'0'),		
			('3','Hack','Me','1337',MD5('charley'),'{$avatarUrl}1337.jpg',NOW(),'0'),		
			('4','Pablo','Picasso','pablo',MD5('letmein'),'{$avatarUrl}pablo.jpg',NOW(),'0'),		
			('5','Bob','Smith','smithy',MD5('password'),'{$avatarUrl}smithy.jpg',NOW(),'0');";		
		if(!mysqli_query($GLOBALS["___mysqli_ston"],$insert)){		
			//dvwaMessagePush( "Data could not be inserted into 'users' table<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["		___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
			//dvwaPageReload();
		}
		//dvwaMessagePush( "Data inserted into 'users' table." );
		
		
		// Create guestbook table		
		$create_tb_guestbook = "CREATE TABLE guestbook (comment_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,comment varchar(300),name varchar(100),PRIMARY KEY (comment_id));";		
		if(!mysqli_query($GLOBALS["___mysqli_ston"],$create_tb_guestbook)){		
			//dvwaMessagePush( "Table could not be created<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : 		(($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
			//dvwaPageReload();
		}
		//dvwaMessagePush( "'guestbook' table was created." );
		
		
		// Insert data into 'guestbook'		
		$insert = "INSERT INTO guestbook VALUES ('1','This is a test comment.','test');";
		if(!mysqli_query($GLOBALS["___mysqli_ston"],$insert)){		
			//dvwaMessagePush( "Data could not be inserted into 'guestbook' table<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($		GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
			//dvwaPageReload();
		}
		//dvwaMessagePush( "Data inserted into 'guestbook' table." );
		
		
		// Copy .bak for a fun directory listing vuln
		$conf = DVWA_WEB_PAGE_TO_ROOT.'config/config.inc.php';
		$bakconf = DVWA_WEB_PAGE_TO_ROOT.'config/config.inc.php.bak';
		if (file_exists($conf)) {
			// Who cares if it fails. Suppress.
			@copy($conf, $bakconf);
		}		

		//redirect setup page
		//dvwaMessagePush( "First time using DVWA.<br />Need to run 'setup.php'." );
		//dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'setup.php' );
	}

	$query  = "SELECT * FROM `users` WHERE user='$user' AND password='$pass';";
	$result = @mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '.<br />Try <a href="setup.php">installing again</a>.</pre>' );
	if( $result && mysqli_num_rows( $result ) == 1 ) {    // Login Successful...
		dvwaMessagePush( "You have logged in as '{$user}'" );
		dvwaLogin( $user );
		dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'index.php' );
	}

	// Login failed
	dvwaMessagePush( 'Login failed' );
	dvwaRedirect( 'login.php' );
}

$messagesHtml = messagesPopAllToHtml();

Header( 'Cache-Control: no-cache, must-revalidate');    // HTTP/1.1
Header( 'Content-Type: text/html;charset=utf-8' );      // TODO- proper XHTML headers...
Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );     // Date in the past

// Anti-CSRF
generateSessionToken();

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">

<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>

		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>Login :: Damn Vulnerable Web Application (DVWA) v" . dvwaVersionGet() . "</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/login.css\" />

	</head>

	<body>

	<div id=\"wrapper\">

	<div id=\"header\">

	<br />

	<p><img src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/images/login_logo.png\" /></p>

	<br />

	</div> <!--<div id=\"header\">-->

	<div id=\"content\">

	<form action=\"login.php\" method=\"post\">

	<fieldset>

			<label for=\"user\">Username</label> <input type=\"text\" class=\"loginInput\" size=\"20\" name=\"username\"><br />


			<label for=\"pass\">Password</label> <input type=\"password\" class=\"loginInput\" AUTOCOMPLETE=\"off\" size=\"20\" name=\"password\"><br />

			<br />

			<p class=\"submit\"><input type=\"submit\" value=\"Login\" name=\"Login\"></p>

	</fieldset>

	" . tokenField() . "

	</form>

	<br />

	{$messagesHtml}

	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />

	<!-- <img src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/images/RandomStorm.png\" /> -->
	</div > <!--<div id=\"content\">-->

	<div id=\"footer\">

	<p>" . dvwaExternalLinkUrlGet( 'http://www.dvwa.co.uk/', 'Damn Vulnerable Web Application (DVWA)' ) . "</p>

	</div> <!--<div id=\"footer\"> -->

	</div> <!--<div id=\"wrapper\"> -->

	</body>

</html>";

?>
