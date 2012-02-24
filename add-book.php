<?php

/* File: add-book.php
 * Author: Rachael Stedman
 * 
 * Description: 
 * this php file takes the given isbn, finds the associated book information,
 * and prints a table row containing the book cover image, book information,
 * and form fields for price, condition, courses, and comments 
 * 
 * Created: 5/09/2010
 * Last Modified: 5/17/2010
 */

require_once("MDB2.php");
require_once("obooks-dsn.inc");
require_once("scripts/html-generator-functions.php"); 
require_once("scripts/database-helper-functions.php");
require_once("scripts/add-functions.php"); 

if(isset($_GET['isbn']) && isset($_GET['bid'])) {
	$isbn = $_GET['isbn'];
	$bid = $_GET['bid'];
	
	/* connect to the database */
	$dbh = db_connect($obooks_dsn);
	
	/* retrieve other relevant book information */
	$arr = find_book($dbh,$isbn);
	
	/* if the book is not already in the database 
	 * and the isbn is valid, add the book to the database 
	 * and then retrieve book information */
	if ($arr == null && add_book($dbh,$isbn)) {
		$arr = find_book($dbh,$isbn);
	}
	
	/* if $arr is still null, isbn was invalid */
	if ($arr == null) {
		echo "invalid";
	} else {
		print_book_form($isbn, $bid, $arr);
	}
}

?>