<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	xmms.php - an include to the main page / music player tab
**
**
*******************************************************************/
?>
<div id="playerStatus">
	<div id="statusPosition"></div> 
	<span id="statusTitle"></span> 
	<a href="javascript:xmmsCommand('current');">[refresh]</a>
</div>
<div id="progressbar" style="clear:both;"></div>
<div id="playerControls" class="ui-widget-header ui-corner-all">
	<div style="position:relative">
		<div>
			<button style="margin-left:8px;" id="rew10">back 10</button>
			<button id="beginning">previous</button>
			<button id="rewind">rewind</button>
			<button id="play">play/pause</button>
			<button id="stop">stop</button>
			<button id="forward">fast forward</button>
			<button id="end">next</button>
			<button id="fwd10">next 10</button>
		</div>
	</div>
</div>
<div class="ui-widget-header" style="position:relative">
	<select id="playlistSelector"></select>
	<input id="search" placeholder="Search..." />
	<button id="go">go</button>
</div>
<div id="xmmsMain">
	<ul id="playlist">
	</ul>
	<div id="searchResults">
<pre>
This
is the 
search 
results
you 
should
not
see this
</pre>
	</div>
</div>
