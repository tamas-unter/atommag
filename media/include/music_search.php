<?php
Header("Content-type: application/json");
if(isset($_GET["q"])){
	//	init db
	if($db=new SQLiteDatabase("/home/pt/.config/xmms2/medialib.db")){
		$result=array();
	//	plain search
	$q=@$db->query(" select distinct key from media where key like \"t%\"");
	if($q === false)$result[]="nincs tábla";
	else{
		$result=$q->fetch_all(SQLITE_ASSOC);
	}
	//	artist
	//	title
	//	album
	//	url
	}	else{
	$result=array();
	$result[]="kamu";
	$result[]="meg";
	}	
}else $result="nodb";
print json_encode($result);
?>
