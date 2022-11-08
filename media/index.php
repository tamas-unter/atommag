<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	index.php - main page
**
**
*******************************************************************/
?>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>ATOMMAG media controller</title>
	<link rel="stylesheet" href="lib/jquery-ui-1.10.0.custom.css" />
	<link rel="stylesheet" href="media.css" />
	<script src="lib/jquery-1.9.0.js"></script>
	<script src="lib/jquery-ui-1.10.0.custom.js"></script>
	<script src="lib/underscore.js"></script>
	<script src="lib/backbone.js"></script>
	<script src="controls.js"></script>
</head>
<body>
<div style="width:100%; height:100%; ">
	<div id="volume"></div> 

	<div id="tabs">
<?php
/*************		TABS	*************************/
$tabs=array(
	"xmms"=>"Zene",
	"series"=>"Sorozatok",
	"video"=>"Film",
	"radio"=>"Rádió",
	"photo"=>"Diavetítés",
	"settings"=>"Beállítások"
);
print"<ul>";
foreach($tabs as $key=>$value){
	print"<li><a href=\"#$key\"><span>$value</span></a></li>";
}
print"</ul>";
foreach($tabs as $key=>$value){
	print"<div id=\"$key\">";
	require("./include/".$key.".php");
	print"</div>";
}
?>
	</div>
</div>

</body>
</html>
