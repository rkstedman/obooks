<?php



/* returns true if email address is valid, false otherwise
 * code borrowed from 
 * http://www.linuxjournal.com/article/9585?page=0,1
 */
function valid_email($email) {
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
    {
       $isValid = false;
    }
    else
    {
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.')
		{
			// local part starts or ends with '.'
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
		{
			// character not valid in local part unless 
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/',
				str_replace("\\\\","",$local)))
			{
				$isValid = false;
			}
		}
		if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
		{
			// domain not found in DNS
			$isValid = false;
		}
   }
   return $isValid;
}

/* returns true if price entered is valid
 * otherwise, returns false
 */
function valid_price($price) {
	$isValid = true;

	if(empty($price)) {
		$isValid = false;
	} elseif(!preg_match('/^\d{1,3}$|^\d{1,3}\.\d{0,2}$/',$price)) {
		$isValid = false;
	}
	
	return $isValid;
}

/* returns true if the provided text contains none of
 * the dirty words specified in dirtywords.txt 
 */
function no_profanity($text) {
	$isValid = true;
	
	$dirtywords = "./scripts/dirtywords.txt";
	$fh = fopen($dirtywords, 'r');
	while(1):
		$word = trim(fgets($fh));
		if (empty($word)) {
			break;
		}
		if(preg_match('/'.$word.'/',$text)) {
			$isValid = false;
		}
	endwhile;
	fclose($fh);
	return $isValid;
}

/* test functions */

function test_valid_price() {
	$regex = '/^\d{1,3}$|^\d{1,3}\.\d{0,2}$/';
	$old_regex = '/\d{1,3}($|\.\d{1,2}$)/';
	
	echo "<p> preg_match: 10".preg_match($regex,'10')."</p>";
	echo "<p> preg_match: 10.0".preg_match($regex,'10.0')."</p>";
	echo "<p> preg_match: 10.00".preg_match($regex,'10.00')."</p>";
	echo "<p> preg_match: abc".preg_match($regex,'abc')."</p>";
	echo "<p> preg_match: 10.000".preg_match($regex,'10.000')."</p>";
	echo "<p> preg_match: *h-".preg_match($regex,'*h-')."</p>";
	echo "<p> preg_match: 1000".preg_match($regex,'1000')."</p>";
}

function test_valid_email() {

	$pass = true;
	/* All of these should succeed: */
	$pass &= valid_email("dclo@us.ibm.com");
	$pass &= valid_email("abc\\@def@example.com");
	$pass &= valid_email("abc\\\\@example.com");
	$pass &= valid_email("Fred\\ Bloggs@example.com");
	$pass &= valid_email("Joe.\\\\Blow@example.com");
	$pass &= valid_email("\"Abc@def\"@example.com");
	$pass &= valid_email("\"Fred Bloggs\"@example.com");
	$pass &= valid_email("customer/department=shipping@example.com");
	$pass &= valid_email("\$A12345@example.com");
	$pass &= valid_email("!def!xyz%abc@example.com");
	$pass &= valid_email("_somename@example.com");
	$pass &= valid_email("user+mailbox@example.com");
	$pass &= valid_email("peter.piper@example.com");
	$pass &= valid_email("Doug\\ \\\"Ace\\\"\\ Lovell@example.com");
	$pass &= valid_email("\"Doug \\\"Ace\\\" L.\"@example.com");
	/* All of these should fail:*/
	$pass &= !valid_email("abc@def@example.com");
	$pass &= !valid_email("abc\\\\@def@example.com");
	$pass &= !valid_email("abc\\@example.com");
	$pass &= !valid_email("@example.com");
	$pass &= !valid_email("doug@");
	$pass &= !valid_email("\"qu@example.com");
	$pass &= !valid_email("ote\"@example.com");
	$pass &= !valid_email(".dot@example.com");
	$pass &= !valid_email("dot.@example.com");
	$pass &= !valid_email("two..dot@example.com");
	$pass &= !valid_email("\"Doug \"Ace\" L.\"@example.com");
	$pass &= !valid_email("Doug\\ \\\"Ace\\\"\\ L\\.@example.com");
	$pass &= !valid_email("hello world@example.com");
	$pass &= !valid_email("gatsby@f.sc.ot.t.f.i.tzg.era.l.d.");
	
	if ($pass)
	{
	   echo "<p> Email test worked.</p>\n";
	}
	else
	{
	   echo "<p> Email test failed.</p>\n";
	}
}
	
?>