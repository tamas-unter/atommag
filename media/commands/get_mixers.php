<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	get_mixers.php - get volume values from the output mixers 
**
**
*******************************************************************/

header('Content-type: application/json');
// relative path... 
// ## removed ref '&' from $volumes - php 5.4
$result=exec("./get_mixers.sh",$volumes);
if(!$result) echo "gondvan";

print"{ \"Volume\": {";
print"\"Master\": {\"Mono\": ".$volumes[0]."}";
//print"\"PCM\": {\"Left\": ".$volumes[1].",\"Right\": ".$volumes[2]."},";
//print"\"Front\": {\"Left\": ".$volumes[3].",\"Right\": ".$volumes[4]."}";
print"}}";
?>
