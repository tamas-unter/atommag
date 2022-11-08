<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	check_players.php - which media apps are running?
**
**
*******************************************************************/

header('Content-type: application/json');
// relative path... 
// ## removed & from $players (=php 5.4 suggests not using references)
$result=exec("./check_players.sh",$players);
if(!$result) echo "gondvan";

print"{ ";
$i=0;
foreach ($players as $p){
	print $p; if ($i++<count($players)-1) print",";
}
print" }";
?>
