<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Obooks</title>
<link href="styles/layout.css" rel="stylesheet" type="text/css" />
<link href="styles/style.css" rel="stylesheet" type="text/css" />
<!-- jQuery library -->
<script type="text/javascript" src="./scripts/jquery/jquery-1.4.2.min.js"></script>
<!-- jCarousel library -->
<script type="text/javascript" src="./scripts/jquery/jquery.jcarousel.min.js"></script>
<link rel="stylesheet" type="text/css" href="./styles/skins/tango/skin.css" />

<!-- 
File: index.php
Author: Rachael Stedman

Description: 
This is the Obooks main page. From here, the user can browse books, 
search books, and post books.

Created: 4/8/2010
Last Modified: 4/15/2010
 -->

<script type="text/javascript">

jQuery(document).ready(function() {
    jQuery('#mycarousel').jcarousel();
});

</script>


</head>

<?php
	require_once("MDB2.php");
	require_once("obooks-dsn.inc");
	require_once("scripts/html-generator-functions.php");
	require_once("scripts/database-helper-functions.php");
	require_once("scripts/add-functions.php");

	echo "<body class='oneColElsCtr'>\n";
	echo "<div id='container'>\n\n";
	header_image();
	navbar();
	echo "<div id='mainContent'>\n";
	
	if(isset($_GET['pid'])) {
		$pid = $_GET['pid'];
		/* display book page for post with this pid */
    	$dbh = db_connect($obooks_dsn); // connect to the database
		print_post($dbh, $pid);
		
	} elseif(isset($_GET['search'])) {
		/* display book page for book with this isbn */
    	$dbh = db_connect($obooks_dsn); // connect to the database
		//search_posts_by_isbn($dbh,$_GET['search']);
		search_all($dbh,$_GET['search']);
		
	} else {
		if(isset($_GET['remove'])) {
			/* remove the given post */
			$dbh = db_connect($obooks_dsn); // connect to the database
			remove_post($dbh,$_GET['remove']);
		}
		/* print newly added etc */	
		echo "<h2> Newly Added </h2>\n";
		/* connect to the database */
		$dbh = db_connect($obooks_dsn);
		
		/* search for most recent books */
		print_book_carousel($dbh);
	}
	echo "</div><!-- end #mainContent -->\n";
?>

<!-- end #container --></div>
</body>
</html>
