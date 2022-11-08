<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	xmms_db.php - query medialib
**
**	requires php_sqlite3
*******************************************************************/
class MediaLib extends SQLite3
{
	function __construct(){
		$dbfile="/home/pt/.config/xmms2/medialib.db";
		$this->open($dbfile);
	}
}

$db=new MediaLib();
if(isset($_GET['c'])){
	//$ret= array();
	switch($_GET['c']){
		case "current":
			$q="SELECT value FROM collectionattributes 
				JOIN collectionlabels ON name=\"_active\" 
				AND collectionlabels.collid=collectionattributes.collid 
				AND key=\"position\"";
			$result=$db->query($q);
			$ret=$result->fetchArray(1);
			$position = $ret['value'];
			$q="SELECT * FROM media 
				JOIN collectionidlists ON mid=id 
				JOIN collectionlabels ON name=\"_active\" 
				AND collectionlabels.collid=collectionidlists.collid 
				WHERE position=$position";
			$result=$db->query($q);
			while($re=$result->fetchArray()){
				switch($re['key']){
					case "album":
						$ret['album']=$re['value'];
						$ret['position']=$re['position'];
						$ret['mid']=$re['mid'];
					break;
					case "comment":
					case "description":
					case "duration":
					case "picture_front":
					case "title":
					case "url":
					case "track_id":
					case "tracknr":
					case "performer":
					case "artist":
					case "title":
					case "album":
						$ret[$re['key']]=$re['value'];
					break;
				}
			};
		break;
		case "tracklist":
			$q="SELECT position,mm.key,mm.value, (position=collectionattributes.value) AS current FROM collectionidlists 
				JOIN collectionlabels on collectionlabels.collid=collectionidlists.collid AND name=\"_active\"
				JOIN collectionattributes ON collectionattributes.collid=collectionlabels.collid 
					AND position>=collectionattributes.value-5
					AND position<collectionattributes.value+150
				JOIN media mm ON collectionidlists.mid=mm.id AND mm.key in (\"artist\", \"title\", \"album\", \"performer\", \"track_id\", \"tracknr\")
				ORDER BY position
				LIMIT 0,170;
			";
			$i=0;
			while(!($result=$db->query($q))&&$i++<5) sleep(5);
			if (!$result) die ("DB STILL LOCKED!");
			while($re=$result->fetchArray()){
				$ret[$re['position']][$re['key']]=$re['value'];
				$ret[$re['position']]['current']=$re['current'];
			}
		break;
		case "search":
			if(isset($_GET["q"])){
				$pattern=$_GET["q"];
				if(isset($_GET["key"])){
					$key=$_GET["key"];
				//	foreach(array("artist","title","album") as $key)
					{
						$q="SELECT DISTINCT value FROM media WHERE key=\"$key\" AND value LIKE \"%$pattern%\"";
						$result=$db->query($q);
						while($re=$result->fetchArray()){
							//$ret[$key][]=$re[0];
							$ret[]=$re[0];
						}
					}
				}	else{
					$q="SELECT id,value FROM media WHERE key=\"url\" AND value LIKE \"%$pattern%\"";
					$result=$db->query($q);
					while($re=$result->fetchArray()){
						$ret[$re[0]]=$re[1];
					}
				}
			}	//else $ret[]="egy";
		break;
		case "locate":
			if(isset($_GET["key"]) && isset($_GET["value"])){
				$q="SELECT m1.id, m2.key, m2.value 
				FROM media m1 JOIN media m2 ON m1.id=m2.id AND 
				m1.key=\"".$_GET["key"]."\" and m1.value=\"".$_GET["value"]."\"
				WHERE m2.key=\"album\" OR m2.key=\"artist\" OR m2.key=\"title\" OR m2.key=\"duration\"
				";
				//OR m2.key=\"url\" 
				$result=$db->query($q);
				while($re=$result->fetchArray(2)){
					$ret[$re[0]][$re[1]]=$re[2];
					//$ret[]=$re;
				}
			}
			else if(isset($_GET["mid"])){
				$q="SELECT * FROM CollectionIdlists WHERE mid=".$_GET["mid"];
				$result=$db->query($q);
				if($re=$result->fetchArray(1)) $ret=$re;
			}
		break;
		case "playlist":
			$q="SELECT name, collid=(SELECT collid FROM collectionlabels WHERE name=\"_active\") 
				FROM collectionlabels WHERE name!=\"_active\";";
			$result=$db->query($q);
			while($re=$result->fetchArray()){
				$ret[]=array($re[0],($re[1] ? true:false));
			}
		break;
	}
	header('Content-type: application/json');
	print json_encode($ret);
}

?>
