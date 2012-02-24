<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- 
File: postform.php
Author: Rachael Stedman

Description: 
The page presents a form to the user for posting books for sale.
They provide their name and email address and then can add
books by isbn to a list of books to post. Removal functionality is
in the works.

Created: 4/8/2010
Last Modified: 4/15/2010
 -->

<title>Obooks</title>
<link href="styles/layout.css" rel="stylesheet" type="text/css" />
<link href="styles/style.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
<!--hide from old browsers

	/* use a global variable to keep track of the form label ids for
	 * each book that is currently in the users list 
	 * we need a unique id for each book added */
	<?php 
		if(isset($_POST['bids'])) {
			if ($_POST['bids'] != "0" && empty($_POST['bids'])) {
				echo "var bids = [];";
			} else {
				echo "var bids = ['".preg_replace('/-/','\',\'',$_POST['bids'])."'];";
			}
		} else { 
			echo "var bids = [];";
		}
	?>

	/* when the add button in the form is clicked, this function
	 * is called to add a book form row */
	function add_book() {
		//alert("add book!");
		var form = document.getElementById('sellerForm');			
		var isbn = form.elements["isbn"].value;
		isbn.replace(/^\s+|\s+$/g,""); /* trim entry */
		if (isbn.length == 0) {
			alert("Please enter an isbn!");
			return;
		} else {
			var new_bid;
			if (bids.length == 0) {
				new_bid = 0;
			} else {
				new_bid = parseInt(bids[bids.length-1])+1;
			}
			var url = "add-book.php?isbn="+isbn+"&bid="+new_bid;
			
			//alert("Making Ajax request of "+url);
			
			/* Ajax request */
			var xhr = new XMLHttpRequest();
			xhr.onreadystatechange=function()
			{
				if (xhr.readyState==4 && xhr.status==200)
				{
					var oldHTML = document.getElementById('books-added').innerHTML;
					var newHTML = xhr.responseText;
					if (/invalid/.test(newHTML)) {
						alert("The isbn you entered is invalid. Please enter a valid isbn.");
					} else {
						document.getElementById('books-added').innerHTML = oldHTML + newHTML;
						bids.push(new_bid);
					}
				}
			}
			xhr.open("GET", url);
			xhr.send(null);
			save();
		}
	}

	/* this function is currently not called anywhere */
	function remove_book(bid) {
		bids.splice(bids.indexOf(bid), 1);
		save();
	}

	/* save the current list of book ids stored in the array bids
	 * in a hidden form field 
	 * necessary to rebuild user's book list if page is refreshed */
	function save() {
		//alert("Save!");
		var newHTML = "\n";
		newHTML = newHTML + "\n<input type=\"hidden\" name=\"bids\" value=\""+bids.join("-")+"\"/>";
		document.getElementById('bids-field').innerHTML = newHTML;
	}
	
//-->
</script>
	
</head>

<?php
	require_once("MDB2.php");
	require_once("obooks-dsn.inc");
	require_once("scripts/html-generator-functions.php");
	require_once("scripts/database-helper-functions.php");
	require_once("scripts/add-functions.php");
	require_once("scripts/validation-functions.php");
	
	/* for debugging */
	//echo "<h1>".implode("-",$_POST)."</h1>";
	
	/* if user form entries are all valid
	 * we won't want to display the form
	 */
	$postform = true;
	
	/* initialize all form values
	 * if the user entered values, they will be overwritten
	 */
	$firstname = "";
	$lastname  = "";
	$email     = "";
	$bids      = null;
	
	/* initialize arrays for prices and other individual book info */
	$isbns      = array();
	$prices     = array();
	$conditions = array();
	$courses    = array();
	$comments   = array();
	
	/* based on user mistakes build warning statement to be printed */
	$warning = "";
	
	/* if the form is filled out, process the information */
	if(isset($_POST['firstname']) || isset($_POST['lastname']) || isset($_POST['email']) || isset($_POST['bids'])) {
		$firstname = $_POST['firstname'];
		$lastname  = $_POST['lastname'];
		$email     = $_POST['email'];
		
		/* we need to check "0" separately because  
		 * the empty function counts "0" as empty */
		if ($_POST['bids'] == "0" || !empty($_POST['bids'])) {
			$bids = explode("-",$_POST['bids']);
		}
		
		/* validate form fields */
		if(empty($firstname) || empty($lastname)) {
			$warning = $warning."<li>You must enter your name.</li>\n";
		}
		if(!valid_email($email)) {
			$warning = $warning."<li>The email address you provided is invalid.</li>\n";	
		}
		if (is_null($bids)) {
			$warning = $warning."<li>You must include at least one book to post. Please add a book by entering the ISBN and clicking 'add'.</li>\n";
		} else {
			/* build arrays for each book post attribute */
			foreach($bids as $bid) {
				$isbns[$bid] = $_POST['isbn-'.$bid];
				$prices[$bid] = $_POST['price-'.$bid];
				$conditions[$bid] = $_POST['condition-'.$bid];
				$courses[$bid] = $_POST['course-'.$bid];
				$comments[$bid] = $_POST['comment-'.$bid];

				$valid_prices = true;
				$valid_conditions = true;
				
				if(!valid_price($prices[$bid])) {
					$valid_prices = false;
				}				
				if(empty($conditions[$bid])) {
					$valid_conditions = false;
				}
				/* in an attempt to keep the site PG13 
				 * we'll test text entries for profanity */
				$profanity = false;
				if(!no_profanity($conditions[$bid])) {
					$profanity = true;
				}
				if(!no_profanity($courses[$bid])) {
					$profanity = true;
				}	
				if(!no_profanity($comments[$bid])) {
					$profanity = true;
				}			
			}	
			if (!$valid_prices) {
				$warning = $warning."<li>Enter your offer price as a decimal between 0.00 and 99.99 </li>\n";
			}
			if (!$valid_conditions) {
				$warning = $warning."<li>You must include a description of each book's condition.</li>\n";
			}
			if($profanity) {
				$warning = $warning."<li>Please refrain from using profanity.</li>\n";
			}
		}
		
		/* if the form was filled out correctly, process the information */
		if (empty($warning)) {
			$postform = false;
			
			echo "<body class='oneColElsCtr'>\n";
			echo "<div id='container'>\n\n";
			header_image();
			navbar();
			echo "<div id='mainContent'>\n";
			
			echo "<p> Your information has been saved. <br /><br />\n",
			"<strong>Seller:</strong> $firstname $lastname <br />\n",
			"<strong>Email:</strong> $email <br /><br />\n",
			"<strong>Books:</strong>\n<ul>\n";
			foreach($bids as $bid) {
				echo "\t<li> ISBN: ".$isbns[$bid].", Price: ".$prices[$bid].", Condition: ".$conditions[$bid];
				if (!empty($courses[$bid])) {
					echo ", Courses: ".$courses[$bid];
				}
				if (!empty($comments[$bid])) {
					echo ", Comments: ".$comments[$bid];
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
			/* connect to the database */
			$dbh = db_connect($obooks_dsn);
			/* add the seller to the database */
			add_seller($dbh,$firstname,$lastname,$email);
			foreach($bids as $bid) {
				add_post($dbh,$isbns[$bid],$email,$prices[$bid],$conditions[$bid],$courses[$bid],$comments[$bid]);
			}
			echo "<h2><a href='index.php'> >> Browse Books</a> </h2>\n";
		}

	}
	if($postform) {
		echo "<body class='oneColElsCtr'>\n";
		echo "<div id='container'>\n\n";
		header_image();
		echo "<div id='navbar'></div>";
		echo "<div id='navbar2'><a href='index.php'> << search books</a> </div>";
		echo "<div id='mainContent'>\n";
		
		if (!empty($warning)) {
			echo "<div style=\"color:red; font-size:90%;\">There is a problem with the information you provided:\n",
			"<ul>\n",
			$warning,
			"</ul>\n",
			"Please correct your form entries and re-submit the form.<br /><br /></div>";
		}
		/* connect to the database */
		$dbh = db_connect($obooks_dsn);
		postform($dbh,$firstname,$lastname,$email,$bids,$isbns,$prices,$conditions,$courses,$comments);
		
	}
	echo "</div><!-- end #mainContent -->\n";
	
?>
    
<!-- end #container --></div>
</body>
</html>