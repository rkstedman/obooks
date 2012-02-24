<?php

/* File: search-functions.php
 * Author: Rachael Stedman
 * 
 * Description: 
 * functions to accompany obooks search interface
 * 
 * Created: 4/15/2010
 * Last Modified: 5/15/2010
 */

require_once("scripts/html-generator-functions.php");
require_once("scripts/database-helper-functions.php");

/* searches the book table for isbn '$search' */ 
function search_posts_by_isbn($dbh,$search) {
    $data = array($search); 
    $types = array('text');
	$sql = "SELECT pid,isbn,email,price FROM post where isbn=?";
	$resultset = prepared_query($dbh,$sql,$data,$types);
	
	// get all the results
	$rows = $resultset->fetchAll(MDB2_FETCHMODE_ASSOC);
	
	return $rows;
}

/* searches the book table for title '$search' */ 
function search_books_by_title($dbh,$search) {
    $data = array('%' . $search . '%'); 
    $types = array('text');
	$sql = "SELECT isbn FROM book where title like ?";
	$resultset = prepared_query($dbh,$sql,$data,$types);
	
	// get all the results
	$rows = $resultset->fetchAll(MDB2_FETCHMODE_ASSOC);
	// find out how many results there were
	$matches = $resultset->rowCount();

	$results = array();
	for ($i=0; $i<$matches; $i++) {
		$results = array_merge( $results, search_posts_by_isbn($dbh,$rows[$i]['isbn']) );
	}
	return $results;	
}

/* searches the book table for author '$search' */ 
function search_books_by_author($dbh,$search) {
    $data = array('%' . $search . '%'); 
    $types = array('text');
	$sql = "SELECT isbn FROM book where author like ?";
	$resultset = prepared_query($dbh,$sql,$data,$types);
	
	// get all the results
	$rows = $resultset->fetchAll(MDB2_FETCHMODE_ASSOC);
	// find out how many results there were
	$matches = $resultset->rowCount();
	
	$results = array();
	for ($i=0; $i<$matches; $i++) {
		$results = array_merge( $results, search_posts_by_isbn($dbh,$rows[$i]['isbn']) );
	}
	return $results;
}

/* searches database tables for keyword '$search'
 * if only one match, displays page, otherwise lists hyperlinks for results */
function search_all($dbh,$search) {

	$isbn_rows = search_posts_by_isbn($dbh,$search);
	$isbn_matches = count($isbn_rows);

	$author_rows = search_books_by_author($dbh,$search);
	$author_matches = count($author_rows);
	
	$title_rows = search_books_by_title($dbh,$search);
	$title_matches = count($title_rows);
	 
	/* find total number of matches */
	$matches = $isbn_matches +  $author_matches + $title_matches;
	
	if ($matches==0) {
		echo "<p>Sorry, there were no matches for '" . $search . "' </p> \n";
		
	} elseif ($matches==1) {
		if ($isbn_matches == 1) {
			$rows = $isbn_rows;
		} elseif ($author_matches == 1) {
			$rows = $author_rows;
		} elseif ($title_matches == 1) {
			$rows = $title_rows;
		} 
		echo "<p><strong>There was ".$matches." book match. </strong></p>";
		print_post($dbh,$rows[0]['pid']);
		
	} else {
		echo "<p><strong>There are ".$matches." book matches. </strong></p>";
		echo "<table class='book-list'>\n";
		for ($i=0; $i<$isbn_matches; $i++) {
			print_post_link($dbh,$isbn_rows[$i]['pid']);
		}
		for ($i=0; $i<$author_matches; $i++) {
			print_post_link($dbh,$author_rows[$i]['pid']);
		}
		for ($i=0; $i<$title_matches; $i++) {
			print_post_link($dbh,$title_rows[$i]['pid']);
		}
		echo "</table>";
	}
}

/* find the post with given isbn and email in the database
 * return price, condition, and relevant courses 
 * if not found, returns null 
 * note that the post table is searched, not the book table 
 * because we want books that are for sale */
function find_post($dbh,$pid) {
	$data = array($pid); 
    $types = array('text');
	$sql = "SELECT post.pid,post.isbn,post.email,post.price,post.condition,post.course,post.comments FROM book INNER JOIN post ON book.isbn = post.isbn WHERE post.pid = ?";
	
	$resultset = prepared_query($dbh,$sql,$data,$types);
	
	$arr = null;
	
	// we know there will only be one result since we are searching by isbn and email
	if( $row = $resultset->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$arr = $row;
	}
	
	return $arr;
}
 

/* find the seller with given email in the database
 * if not found, returns null */
function find_seller($dbh,$email) {
	$data = array($email); 
    $types = array('text');
	$sql = "SELECT email,firstname,lastname FROM seller where email=?";
	$resultset = prepared_query($dbh,$sql,$data,$types);
	$arr = null;
	
	// we know there will only be one result since we are searching by isbn
	if( $row = $resultset->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
		$arr = $row;
	}
	
	return $arr;
}

/* find the book with given isbn in the database
 * if not found, returns null */
function find_book($dbh,$isbn) {
	$data = array($isbn); 
    $types = array('text');
	$sql = "SELECT isbn,title,author,publisher FROM book where isbn=?";
	$resultset = prepared_query($dbh,$sql,$data,$types);
	$arr = null;
	
	// we know there will only be one result since we are searching by isbn
	if( $row = $resultset->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
		$arr = $row;
	}
	
	return $arr;
}

/* find the ten most recently added books */
function find_most_recent($dbh) {
	$data = array(); 
    $types = array();
	$sql = "SELECT post.pid,post.price,book.isbn,title FROM post NATURAL JOIN book ORDER BY posttime";
	$resultset = prepared_query($dbh,$sql,$data,$types);
	
	$arr = array();
	for($i=0;$i<10;$i=$i+1) {
		if( $row = $resultset->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
			$arr[] = $row;
		}
	}
	
	return $arr;
}




?>