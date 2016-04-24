<?php
header('Content-type: text/plain');
header('Content-Disposition: attachment; filename="zapis.gcg"');

require_once "../system.php";
db_open();

$player1=$_GET['p1'];
if ($player1>$_GET['p2']) {
	$player1=$_GET['p2'];
}

$query = "SELECT data FROM ".TBL_GCG." WHERE tour=".$_GET['turniej']." AND round=".$_GET['runda']." AND player1=".$player1.';';

$result = db_query($query);

if ($result) {
    $gcgtext = db_fetch_row($result)[0];
    echo $gcgtext;
}
?>