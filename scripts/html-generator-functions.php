<?php

/* File: html-generator-functions.php
 * Author: Rachael Stedman
 * 
 * Description: 
 * functions that generate html for obooks
 * 
 * Created: 04/8/2010
 * Last Modified: 5/15/2010
 */

require_once("scripts/search-functions.php");
require_once("MDB2.php");
require_once("obooks-dsn.inc");
require_once("scripts/database-helper-functions.php");

/* creates a label and an empty text field */
function textarea($name, $label, $value="") {
    echo "\t<label for=$name>$label</label> <br />\n";
    echo "\t<input type='text' name='$name' value='$value'/>";
}

/* given a legend name and an array of text area names and labels
 * creates fieldset */
function fieldset($legend, $arr) {
    echo "<fieldset>\n";
    echo "\t<legend> $legend </legend>\n";
    foreach ($arr as $name => $label) {
		textarea($name, $label);
		echo "<br /> <br />\n";
    }
}

/* generates html for the form required to post a book for sale */
function postform($dbh,$firstname,$lastname,$email,$bids,$isbns,$prices,$conditions,$courses,$comments) {
	echo "\n<form id=\"sellerForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
	$seller_arr = array("firstname" => "First Name", "lastname" => "Last Name", "email" => "Email");
	echo "<fieldset>\n";
	echo "\t<legend> Seller Information </legend>\n";
	textarea("firstname","First Name",$firstname);
	echo "<br /> <br />\n";
	textarea("lastname","Last Name",$lastname);
	echo "<br /> <br />\n";
	textarea("email","Email",$email);	
	echo "<br /> <br />\n";
	echo "</fieldset>\n";
	echo "<br />\n";
	fieldset("Book Information",array());
	textarea("isbn","Search books by ISBN");
	echo "<input type=\"button\" value=\"add\" onClick=\"add_book()\"/> <br /> \n\n";
	/* create a table that will be filled with book information via javascript
	 * acts like a shopping list, where user can add books and remove them */
	echo "<table id=\"books-added\">";
	/* add any books user already added */	
	if (!is_null($bids)) {
		foreach($bids as $bid) {
			/* retrieve other relevant book information
			 * if book has been added before, we know its in database
			 * so we don't need to check */
			$arr = find_book($dbh,$isbns[$bid]);
			print_book_form($isbns[$bid], $bid, $arr, $prices[$bid],$conditions[$bid],$courses[$bid],$comments[$bid]);
		}
		echo "</table>\n\n";
		echo "<div id=\"bids-field\"> <input type=\"hidden\" name=\"bids\" value=\"".implode("-",$bids)."\"/></div>";
	} else {
		echo "</table>\n\n";
		echo "<div id=\"bids-field\"> <input type=\"hidden\" name=\"bids\" value=\"\"/></div>";
	}
	echo "</fieldset>\n";
	echo "<br /><input type=\"submit\" value=\"Submit\" onClick=\"save()\">";
	echo "</form>\n";
}

/* prints a table row for a book added by the seller 
 * includes the book image, book information, and
 * form fields for price, condition, course, and comments
 */
function print_book_form($isbn, $bid, $arr, $price="", $condition="", $course="", $comment="") {
	$title = $arr['title'];
	$author = $arr['author'];
	$publisher = $arr['publisher'];

	echo "<tr> <td> ";
	book_image($isbn);
	//echo "<br /><input type=\"button\" name=\"remove-$bid\" value=\"Remove\" class=\"remove-btn\" onClick=\"remove_book($bid)\">";
	echo "</td> <td>";
	echo "<h2>$title</h2><br /><em>$author</em> <br /><br /> $publisher <br /> ISBN: $isbn";
	echo "\t<input type='hidden' name='isbn-$bid' value=$isbn>\n <br /><br />";
	echo "\t<label for='price-$bid'>&nbsp Price</label> <br />\n";
	echo "\t$<input type='text' name='price-$bid' size='3' value='$price'>";
	echo "</td> <td class='book-fields'>";
	echo "\t<label for='condition-$bid'>Book Condition</label> <br />\n";
	echo "\t<input type='text' name='condition-$bid' value='$condition'> <br /><br />";
	echo "\t<label for='course-$bid'>Relevant Courses (optional)</label> <br />\n";
	echo "\t<input type='text' name='course-$bid' value='$course'> <br /><br />";
	echo "\t<label for='comment-$bid'>Comments (optional)</label> <br />\n";
	echo "\t<textarea name='comment-$bid' rows='1'  value='$comment'></textarea>";
	echo "</td></tr>";
}

/* prints an image of the book cover with given isbn */
function book_image($isbn, $title="no image available", $price=null, $large=false) {
	if ($large) {
		echo "<div class='book-image-L'><img src='http://covers.openlibrary.org/b/isbn/$isbn-L.jpg?default=false' alt='$title' width='250' height='351'  /></div>";
		if (!empty($price)) {
			echo "<div class='price-tag-L'>$price</div>";
		}
	} else {
		echo "<div class='book-image'><img class='book-image' src='http://covers.openlibrary.org/b/isbn/$isbn-M.jpg?default=false' alt='$title' width='100' height='140' /></div>";
		if (!empty($price)) {
			echo "<div class='price-tag'>$price</div>";
		}
	}
}

/* printers header image for main website pages */
function header_image() {
	echo "<div id='header'>\n",
  	"\t<a href='index.php'><img src='images/header.png' width='520' height='145' alt='Obooks' /></a>\n",
	"</div>\n\n";
}

/* prints navbar div and contents 
 * including search box and post book button */
function navbar() {
	echo "<div id='navbar'>\n",
  	"<table id='navbar'><tr>\n",
    "\t<td id='postbutton'>&nbsp;</td>\n",
    "\t<td id='searchbar'><form method='GET' action='index.php'>\n",
    "\t\t<input name='search' type='text' maxlength='200' />\n",
    "\t\t<input type='submit' value='Search' />\n",
    "\t</form></td>\n",
    "\t<td id='postbutton'><form action='postform.php'>\n",
    "\t\t<input type='submit' value='Post Book' />\n",
    "\t</form></td>\n",
  	"</tr></table>\n",
  	"</div>\n\n";
}

/* print book information in nice format */
function book_info($dbh,$isbn) {
	$arr = find_book($dbh,$isbn);
	$title = $arr['title'];
	$author = $arr['author'];
	$publisher = $arr['publisher'];
	$course = $arr['course'];
	$comments = $arr['comments'];
	
	echo "<div class='bookimg'>\n\t";
	book_image($isbn);
	echo "\n</div><!--end bookimg-->\n";
    echo "<div class='bookinfo'>\n";
	echo "\t<h2 class='bktitle'> $title </h2>\n\t <em> by $author </em> \n";
	echo "\t <span class='lcorners'>&nbsp;</span> <p><strong>Publisher</strong> $publisher </p>\n";
	if ( !is_null($course) && $course != "" ) {
		echo "\t<p><strong>Relevant Course</strong> $course</p>";
	}
	echo "\t<p> <strong>Comments</strong> $comments </p>\n";
	echo "</div><!--end bookinfo-->\n";	
}



/* this function formats and prints a hyperlink for a 
 * book in a single table row 
 */
function print_post_link($dbh,$pid) {
	/* first we need to retrieve all the relevant information */
	$post_arr = find_post($dbh,$pid);
	$isbn = $post_arr['isbn'];
	$email = $post_arr['email'];
	$condition = $post_arr['condition'];
	$course = $post_arr['course'];
	$price = $post_arr['price'];
	
	$book_arr = find_book($dbh,$isbn);
	$title = $book_arr['title'];
	$author = $book_arr['author'];
	$publisher = $book_arr['publisher'];
	
	$seller_arr = find_seller($dbh,$email);
	$firstname = $seller_arr['firstname'];
	
	$mouse_effects = "style=\"margin:0;padding:0;\" onmouseover=\"this.style.background='#77cfed';this.style.cursor='pointer'\" onmouseout=\"this.style.background='white';\" onclick=\"window.location='".$_SERVER['PHP_SELF']."?pid=".$pid."'\"";
	
    echo "<tr $mouse_effects><td>\n";
	book_image($isbn,$title);
	echo "\n</td> <td>\n";
	echo "\t<h2>$title</h2><br /><em>$author</em> <br /><br />\n";
	echo "\t<h2> \$$price </h2><br />\n";
	echo "</td> <td class='book-fields'>\n";
	echo "\t<strong>Seller</strong> <br /> $firstname <br /><br />\n";
	echo "\t<strong>Book Condition</strong> <br /> $condition <br /><br />\n";
	echo "\t<strong>Relevant Courses</strong> <br /> $course \n";
	echo "</td> </tr>\n";	
}

/* print the information for a single book */
function print_post($dbh,$pid) {
	/* retrieve relevant information from all tables */
	$post_arr = find_post($dbh,$pid);
	$book_arr = find_book($dbh,$post_arr['isbn']);
	$seller_arr = find_seller($dbh,$post_arr['email']);

	/* print information */
	echo "<table style=\"width=100%;\" cellpadding='10px'><tr><td>";
	echo "<h1>".$book_arr['title']."</h1>";
	echo "<h2><em>".$book_arr['author']."</em></h2>";
	echo "<p> Seller: ".$seller_arr['firstname']." ".$seller_arr['lastname']."</p>";
	echo "<p> Condition: ".$post_arr['condition']."<br />";
	if (!empty($post_arr['course'])) {
		echo "Relevant Courses: ".$post_arr['course']."<br />";
	}	
	if (!empty($post_arr['comments'])) {
		echo "Other Comments: ".$post_arr['comments']."<br />";
	}
	echo "</p>\n";
	echo "<h1 style=\"background-color:\"> Price: $".$post_arr['price']."</h1>";
	echo "<form><input type=\"button\" value=\"Remove Post\" name=\"remove\" class=\"remove-btn\" onClick=\"window.location='index.php?remove=$pid'\"></form>";
	echo "</td><td style=\"padding-top:30px\">";
	book_image($book_arr['isbn'],"\n no image available",null,true);
	echo "</td></tr></table>";
}

function print_book_carousel($dbh) {
	$arr = find_most_recent($dbh);
	
	echo "<ul id=\"mycarousel\" class=\"jcarousel-skin-tango\">";
		foreach ($arr as $row) {
			echo "\t<li> <a href='index.php?pid=".$row['pid']."'>\n\t\t";
			$dotIndex = strrpos($row['price'], ".");
			//$cents = substr($row['price'], $dotIndex+1);
			$dollars = substr($row['price'], 0, $dotIndex);
			book_image($row['isbn'],$row['title'],"$".$dollars);
			echo "</a> </li>\n";
		}
   		echo "</ul><br /><hr />";
}

?>