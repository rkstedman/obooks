<!doctype html>
<html>
 <?php 
  require_once("scripts/html-generator-functions.php");
?>
<head>
    <script type="text/javascript" src="scripts/jquery/jquery-1.4.2.js"></script>
	<script type="text/javascript" src="scripts/jquery/jquery.form.js"></script> 
	
    <script type="text/javascript">
	<!--hide from old browsers
		function clickedMe() {
			var oldHTML = document.getElementById('atitle').innerHTML;
			var form = document.getElementById('myForm');
			var isbn = form.elements["isbn"].value;
			var newHTML = oldHTML + "<?php addBook("12345") ?>" + "<p>"+isbn+"</p>";
			document.getElementById('atitle').innerHTML = newHTML;
		}
		function get_info() {
			alert("Adding Book Form Row...");
			var form = document.getElementById('myForm');
			var isbn = form.elements["isbn"].value;
			var url = "addBook.php?isbn="+isbn;
			alert("Making Ajax request of "+url);
			// Ajax request, stored in global variable
			var xhr = new XMLHttpRequest();
			xhr.onreadystatechange=function()
			{
				if (xhr.readyState==4 && xhr.status==200)
				{
					var oldHTML = document.getElementById('atitle').innerHTML;
					var newHTML = oldHTML + xhr.responseText;
					document.getElementById('atitle').innerHTML = newHTML;
				}
			}
			xhr.open("GET", url);
			xhr.send(null);
			
			
		}
	//-->
    </script>
  </head>
  
  
  <body>
    <form id="myForm" action="jquery-test.html" method="post"> 
    ISBN: <input type="text" name="isbn" value=""/>
	<input type="button" value="Add Book" onClick="get_info()"/>
	<input type="button" value="Remove All" onClick="document.getElementById('atitle').innerHTML='';"/>
	<table id="atitle"> </table>
    <input type="submit" value="I do nothing" /> 
</form>
  </body>
</html>
