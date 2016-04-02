<?php
header('Content-type: text/plain');
header('Content-Disposition: attachment; filename="zapis.gcg"');
include 'config.php';
$con = mysqli_connect($mysqlhost,$mysqluser, $mysqlpwd, $mysqldbname);
$query = 'SELECT gcg FROM PFSTOURHH WHERE turniej = ' . $_GET['turniej'] . ' AND runda = ' . $_GET['runda'] . ' AND player1 = ' . $_GET['p1'] . ';';
mysqli_set_charset($con, 'utf8');
$result = mysqli_query($con, $query);

if ($result) {
    $gcgtext = mysqli_fetch_array($result)[0];
    print $gcgtext;
}
?>