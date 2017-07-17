<?php
require( "database.php" );

$sql = "select * from orders";
$query = mysql_query( $sql );
checkDBError();

while( $result = mysql_fetch_array( $query ) )
{
	$item = $result['item'];
	$sql = "select forms.ID from forms left join form_headers on forms.ID=form_headers.form left join form_items on form_items.header = form_headers.ID where form_items.ID=$item";
	$query2 = mysql_query( $sql );
	checkDBError();
	
	if( $result2 = mysql_fetch_array( $query2 ) )
	{
		$sql = "update orders set form='" . $result2['ID'] . "' where ID=" . $result['ID'];
		mysql_query( $sql );
		checkDBError();
	}
	else
	{
		mysql_query( "delete from orders where ID=" . $result['ID'] );
	}
}
?>
Done