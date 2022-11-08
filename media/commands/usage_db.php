<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	usage_db.php - save last played & like information
**
**
*******************************************************************/
$db=mysql_connect("localhost","media","media");
mysql_select_db("media");

if(isset($_GET['title'])&&isset($_GET['se'])) {
	$q="INSERT INTO series_history (title,se) VALUES (\"".$_GET['title']."\",\"".$_GET['se']."\")";
	mysql_query($q);
}
$q="SELECT * FROM series_history ORDER BY time_played DESC LIMIT 0,10";
$re=mysql_query($q);
while($res=mysql_fetch_object($re)) $return[]=$res;
mysql_close($db);

header('Content-type: application/json');
print json_encode($return);
?>
