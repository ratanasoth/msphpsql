--TEST--
starts a transaction, delete rows and rollback the transaction
--SKIPIF--

--FILE--
<?php
    require_once("autonomous_setup.php");

    $conn = new PDO( "sqlsrv:Server=$serverName; Database = tempdb ", $username, $password);
    
    $conn->exec("IF OBJECT_ID('Table1', 'U') IS NOT NULL DROP TABLE Table1");
    $conn->exec("CREATE TABLE Table1(col1 CHARACTER(1), col2 CHARACTER(1)) ");
   
    $ret = $conn->exec("insert into Table1(col1, col2) values('a', 'b') ");
    $ret = $conn->exec("insert into Table1(col1, col2) values('a', 'c') ");
   
    //revert the inserts
    $conn->beginTransaction();
    $ret = $conn->exec("delete from Table1 where col1 = 'a'");
    $conn->rollback();
    $stmt = $conn->query("SELECT * FROM Table1");
    
    // Table1 should still have 2 rows since delete was rolled back
    if ( count( $stmt->fetchAll() ) == 2 )
        echo "Transaction rolled back successfully\n";
    else
        echo "Transaction failed to roll back\n";
   
    //drop the created temp table
    $conn->exec("DROP TABLE Table1 ");
   
    //free statement and connection
    $ret=NULL;
    $conn=NULL;
?>
--EXPECT--
Transaction rolled back successfully