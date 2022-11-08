<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	set_mixers.php - main page
**		v=master volume (may contain %)
**
**
*******************************************************************/

$master=$_GET["v"];
// ## removed ref &
$result=exec("./set_mixers.sh $master",$volumes);
header('Content-type: application/json');
print("{\"Status\":\"ok\"}");
?>
