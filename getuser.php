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

.fileUpload {
    position: relative;
    overflow: hidden;
    margin: 10px;
}
.fileUpload input.upload {
    position: absolute;
    top: 0;
    right: 0;
    margin: 0;
    padding: 0;
    font-size: 20px;
    cursor: pointer;
    opacity: 0;
    filter: alpha(opacity=0);
}

</style>

</head>
<body>

<?php
error_reporting(E_ALL);
$q = intval($_GET['q']);

include "config.php";
   
$con = mysqli_connect($mysqlhost,$mysqluser, $mysqlpwd, $mysqldbname);

if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_query($con, "SET NAMES 'utf8'");

mysqli_set_charset('utf8', $con);

$sql="SELECT PFSTOURHH.turniej, PFSTOURHH.gcg, PFSTOURS.name, PFSTOURHH.runda, PFSTOURHH.result1, PFSPLAYER.name_show, PFSTOURHH.player2, PFSTOURHH.result2, PFSTOURHH.host
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
        $order = array($user['name_show'], $q, $row['name_show'], $row['player2'], $row['result1'], $row['result2']);
    }
    else {
        $order = array($row['name_show'], $row['player2'], $user['name_show'], $q, $row['result2'], $row['result1']);
    }
    echo "<td>" . $order[0] . "</td>";
    echo "<td>" . $order[2] . "</td>";
    echo "<td>" . $order[4] . "</td>";
    echo "<td>" . $order[5] . "</td>";
    echo "<td>";

    $temp_fname = $row['turniej'] . '_' . $row['runda'] . '_' . $order[1] . '_' . $order[3] . '.gcg';
    
    if (strlen($row['gcg']) > 30) {
        echo '<a href=board.php?turniej=' . $row['turniej'] . '&runda=' .$row['runda'] . '&p1=' . $order[1] . '&p2=' . $order[3] . '>[zapis]</a> ';
    }
    else {
        echo '<div class="fileUpload btn btn-primary">';
        echo '<span>Dodaj</span>';
        echo '<input type="file" class="upload" ';
        echo 'data-index=' . $i . ' data-turniej=' . $row['turniej'] . ' data-runda=';
        echo $row['runda'] . " data-player1=" . $order[1] . " data-player2=" . $order[3];
        echo " data-p1pts=" . $order[4] . " data-p2pts= " . $order[5];
        echo ' /></div>';
    }
    echo '</td>';
    echo "</tr>";
    $i += 1;
}
echo "</table>";
mysqli_close($con);
?>
</body>
</html>