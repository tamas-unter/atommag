<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	mplayer_actions.php - control via slave & read mplayer output
**			POST!!!!
**
*******************************************************************/

if(isset($_POST['f'])) {
	system("echo \"".$_POST['f']."\" >/tmp/NEXT");
	$cmd="../commands/start_mplayer.sh";// >/dev/null 2>&1 &";
	pclose(popen($cmd,'r'));
	//echo"ok";
}
else{
	if (isset($_GET['c'])){
		system("echo ".$_GET['c']." >/tmp/mp_in.fifo");
	}
}
?>
