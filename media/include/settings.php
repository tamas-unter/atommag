<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	settings.php - include for settings, advanced features
**
**
*******************************************************************/
?>
<pre>itt kell, hogy legyenek:
KILL service - button / link?
hangerőszabályzók (PCM, front  - a master bal oldalt látszik)
	de hozzá számszerű értékeket is...
teszt gombok : hang, video
adatbézis újraizélése (xmms sync, sorozatok)
</pre>
<div>running services:
<ul id="services">
</ul>
<script>
$.ajax({
	dataType:'json',
	async: false,
	url:"commands/check_players.php",
	success:function(response){
		if(response.XMMS2D) $("<li>Music Player</li>").appendTo($("#services"));
		if(response.MPLAYER) $("<li>Video / Series</li>").appendTo($("#services"));
		if(response.MENCODER) $("<li>Media encoding</li>").appendTo($("#services"));
	}
});
</script>
<?php
?>
</div>
