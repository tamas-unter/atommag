<?
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	actions.php
**		in: 
			c (command:"prev"|"next"|"seek"|"toggle"|"play"|"pause"|"jump"), 
				if c="status", nothing's executed, just status is read
			p (parameter:"[+|-]?[\d*]")
**		out: JSON (state, song, position, total)
**
**
*******************************************************************/
Header("Content-type: application/json");
if (isset($_GET['c'])) {
	$value=(isset($_GET['v']) ? $_GET['v'] : "");
	if($_GET['c']!="status"){
		system('nyxmms2 '.$_GET['c'].' '.$value.' > /dev/null');
	}
	//	read status
	$f = popen('nyxmms2 status', 'r');
	$out = '';
	if ($d = fread($f, 1024)) {
		$out.= $d;
	}
	pclose($f);
	$pattern="/^([A-Z][a-z]+): (.*): (\d{2}):(\d{2}) of (\d{2}):(\d{2})/";
	
	preg_match($pattern,$out,$matches);
	$result["state"]=$matches[1];
	$result["song"]=$matches[2];
	
	$pos["min"]=$matches[3];
	$pos["sec"]=$matches[4];
	$total["min"]=$matches[5];
	$total["sec"]=$matches[6];
	
	$result["position"]=$matches[3]*60+$matches[4];
	$result["total"]=$matches[5]*60+$matches[6];
	print json_encode ($result);
}
?>