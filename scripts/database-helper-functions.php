<?php

/* File: database-helper-functions.php
 * Author: Scott Anderson (slightly modified by Rachael Stedman)
 * 
 * Description: 
 * database-related functions to accompany obooks
 * 
 * Created: 4/15/2010
 * Last Modified: 5/15/2010
 */

require_once("MDB2.php");

/* connects to the database and returns a dbh or dies */
function db_connect($dsn) {
  $dbh = &MDB2::factory($dsn);

  if(PEAR::isError($dbh)) {
    die();
  	//die("Error while connecting : " . $dbh->getMessage());
  }
  return $dbh;
}

function prepared_sql($dbh,$sql,$data,$types,$kind) {
    // $dbh is the database
	// $sql is the sql query
    // $types is an array of datatypes of the placeholders
    // $kind should be either MDB2_PREPARE_RESULT or MDB2_PREPARE_MANIP
	
	// We prepare the query and get back a statement handle.  The last
    // argument, MDB2_PREPARE_RESULT says we'll be getting results back.
    // Use MDB2_PREPARE_MANIP if you are doing something like delete,
    // insert or update.
    $sth = $dbh->prepare($sql,$types,$kind);
    if( PEAR::isError($sth) ) {
        //die();
        die("<p><strong>Failed to prepare query $sql, error message : " . $sth->getMessage() . "</strong>");
    }

	// At last, we execute the query
    $resultset = $sth->execute($data);
    if(PEAR::isError($resultset)) {
    	//die();
    	die("<p><strong>Failed on query $sql, error message : " . $resultset->getMessage() . "</strong>");
    }
    return $resultset;
}

/* The return value from prepared_query is a result handle that you can
 * read from using fetchRow() or fetchRow(MDB2_FETCHMODE_ASSOC). */
function prepared_query($dbh,$sql,$data,$types) {
    return prepared_sql($dbh,$sql,$data,$types,MDB2_PREPARE_RESULT);
}

/* Executes an update statement with placeholders.  The return value is
 * the number of affected rows. */
function prepared_statement($dbh,$sql,$data,$types) {
    return prepared_sql($dbh,$sql,$data,$types,MDB2_PREPARE_MANIP);
}

?>