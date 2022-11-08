<?php
$base="/home/pt/.config/xmms2/bindata";
$file="0b1a4a066363a546d3dd6b4ca760e457";

if(isset($_GET['f'])) $file=$_GET['f'];
if(isset($_GET['cmd'])){} else
{
	header("Content-type: image/jpeg");
	readfile($base."/".$file);
}
?>
