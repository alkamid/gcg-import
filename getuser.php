<!DOCTYPE html>
<html>
<head>
<style>
table {
    width: 100%;
    border-collapse: collapse;
}

table, td, th {
    border: 1px solid black;
    padding: 5px;
}

th {text-align: left;}
</style>

</head>
<body>

<?php

$q = intval($_GET['q']);

include "config.php";
   
$con = mysqli_connect($mysqlhost,$mysqluser, $mysqlpwd, $mysqldbname);

if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_query($con, "SET NAMES 'utf8'");

mysqli_set_charset('utf8', $con);

$sql="SELECT PFSTOURHH.turniej, PFSTOURS.name, PFSTOURHH.runda, PFSTOURHH.result1, PFSPLAYER.name_show, PFSTOURHH.player2, PFSTOURHH.result2, PFSTOURHH.host
      FROM PFSTOURHH
      JOIN PFSTOURS
      ON PFSTOURHH.turniej=PFSTOURS.id
      JOIN PFSPLAYER
      ON PFSTOURHH.player2 = PFSPLAYER.id
      WHERE player1 = '".$q."'
      ORDER BY PFSTOURS.name, PFSTOURHH.runda";

$result = mysqli_query($con,$sql);

$sql_singleuser = "SELECT name_show FROM PFSPLAYER WHERE id = '" .$q."';";
$result_singleuser = mysqli_query($con, $sql_singleuser);
$user = mysqli_fetch_array($result_singleuser);
echo "<table>
<tr>
<th>Turniej</th>
<th>Runda</th>
<th>Gracz 1</th>
<th>Gracz 2</th>
<th>Wynik 1</th>
<th>Wynik 2</th>
<th>Zapis</th>
</tr>";

$i = 0;
while($row = mysqli_fetch_array($result)) {
    echo "<tr>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['runda'] . "</td>";
    if ($row['host'] == 1) {
        $order = array($user['name_show'], $q, $row['name_show'], $row['result1'], $row['result2']);
    }
    else {
        $order = array($row['name_show'], $row['player2'], $user['name_show'], $row['result2'], $row['result1']);
    }
    echo "<td>" . $order[0] . "</td>";
    echo "<td>" . $order[2] . "</td>";
    echo "<td>" . $order[3] . "</td>";
    echo "<td>" . $order[4] . "</td>";
    echo "<td><input class='inp' data-index=" . $i ." type='file' name='sortpic' />";
    echo "<button class='upload' data-index=" . $i . " data-turniej=" . $row['turniej'] . " data-runda=" . $row['runda'] . " data-player1=" . $order[1] . ">Dodaj</button></td>";
    echo "</tr>";
    $i += 1;
}
echo "</table>";
mysqli_close($con);
?>
</body>
</html>
