<?php

  /* Functions to invoke the MDB2 methods and check for errors afterwards.
   The script dies with an error message if an error is discovered.  If
   you want to handle errors in a more intelligent or flexible way, you
   should call the underlying MDB2 methods.

   Written by Scott D. Anderson
   Spring 2008
  */

/* connects to the database and returns a dbh or dies */

function db_connect($dsn) {
  $dbh = &MDB2::factory($dsn);

  if(PEAR::isError($dbh)) {
    die("Error while connecting : " . $dbh->getMessage());
  }
  return $dbh;
}

/* A different way to connect.  This sometimes succeeds when the preceding version fails. */

function db_connect2($dsn) {
    $dsn_str = 'mysql://' . $dsn['username'] . ':' . $dsn['password'] . '@' . $dsn['hostname'] . '/' . $dsn['database'];
    // echo "connection string is $dsn_str</br>\n";
    $dbh = &MDB2::connect($dsn_str);
        
    if(PEAR::isError($dbh)) {
        die("Error while connecting : " . $dbh->getMessage());
    }
    $dbh->setFetchMode(MDB2_FETCHMODE_ASSOC);
    return $dbh;
}

/* for simple queries, without placeholders.  Returns a resultset */

function query($dbh,$sql) {
    $resultset = $dbh->query($sql);
    if( PEAR::isError($resultset) ) {
        die("<p><strong>Failed on query <q>$sql</q> with error message: " .
            $resultset->getMessage() . "</strong>");
    }
    return $resultset;
}            

/*  For sql queries/statements with placeholders.  Supply an array of
values as third arg.  Assumes all the arguments are text, which works in
many cases, but is not sufficiently general.  This function can be called
by the user, but is primarily intended as the core behavior of
prepared_query() and prepared_statement().
*/

function prepared_sql($dbh,$sql,$data,$kind) {
    $n = sizeof($data);
    $types = array($n);

    // You have to declare the datatypes of the placeholders.  Because
    // there could be more than one, an array is used.  In this function,
    // the datatype is always 'text'

    for($i=0; $i<$n; $i++) {
        $types[$i] = 'text';
    };

    // echo "<p>n = $n<p>sql = $sql<p>types = $types<p>data = $data\n";

    // $kind should be either MDB2_PREPARE_RESULT or MDB2_PREPARE_MANIP
    $sth = $dbh->prepare($sql,$types,$kind);
    if( PEAR::isError($sth) ) {
        die("<p><strong>Failed to prepare query $sql, error message : " . $sth->getMessage() . "</strong>");
    }

    $resultset = $sth->execute($data);
    if(PEAR::isError($resultset)) {
        die("<p><strong>Failed on query $sql, error message : " . $resultset->getMessage() . "</strong>");
    }
    return $resultset;
}

/* Executes a query with placeholders.  The return value is a result
 handle that you can read from using fetchRow() or
 fetchRow(MDB2_FETCHMODE_ASSOC).
*/

function prepared_query($dbh,$sql,$data) {
    return prepared_sql($dbh,$sql,$data,MDB2_PREPARE_RESULT);
}

/* Executes an update statement with placeholders.  The return value is
 the number of affected rows.
*/

function prepared_statement($dbh,$sql,$data) {
    return prepared_sql($dbh,$sql,$data,MDB2_PREPARE_MANIP);
}

?>
