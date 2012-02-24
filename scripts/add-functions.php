
<?php

/* File: add-functions.php
 * Author: Rachael Stedman
 * 
 * Description: 
 * functions to accompany obooks interface by adding or removing data to the database
 * 
 * Created: 4/15/2010
 * Last Modified: 5/17/2010
 */

/* obtain book information from external library and add to database 
 * returns false if isbn was invalid, true otherwise */
function add_book($dbh,$isbn) {

	/* Get the XML document loaded into a variable */
	$book_xml = 'http://isbndb.com/api/books.xml?access_key=H9QEPWWQ&index1=isbn&value1='.$isbn;
	$ISBNdb = simplexml_load_file($book_xml);
	/* get how many results for given isbn */
	$shown_results = $ISBNdb->BookList->attributes()->shown_results;
	
	/* make sure there is only one result for given isbn */
	if ($shown_results == "1") {	
		$BookData = $ISBNdb->BookList->BookData;
	
		$data = array($isbn,$BookData->Title,$BookData->AuthorsText,$BookData->PublisherText);
		$types = array('text','text','text','text');
	    prepared_statement($dbh,"INSERT INTO book VALUES(?,?,?,?)",$data,$types);
	 	
	    return true;   
    } else {
    	return false;
	}
}

/* add another post to the post table */
function add_post($dbh,$isbn,$email,$price,$condition,$course,$comments) {
	$data = array($email,$isbn,$price,$condition,$course,$comments);
	$types = array('text','text','decimal','text','text','text');
	prepared_statement($dbh,"INSERT INTO post VALUES(0,?,?,?,?,?,?,NOW())",$data,$types);
}
/* add another seller */
function add_seller($dbh,$firstname,$lastname,$email) {
	$data = array($firstname,$lastname,$email);
	$types = array('text','text','text');
	prepared_statement($dbh,"INSERT INTO seller VALUES(?,?,?)",$data,$types);
}

/* remove the post with the given id */
function remove_post($dbh,$pid) {
	$data = array();
	$types = array();
	prepared_statement($dbh,"DELETE FROM post WHERE pid=$pid",$data,$types);
}


?>