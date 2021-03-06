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
$q = intval($_GET['q']);

require_once "../system.php";
db_open();

$sql="SELECT H.turniej, T.name, H.runda, H.result1, P.name_show, H.player2, H.result2, H.host, G.round
      FROM ".TBL_TOURHH." AS H
      JOIN ".TBL_TOURS." AS T
      ON H.turniej=T.id
      JOIN ".TBL_PLAYER." AS P
      ON H.player2 = P.id
	  LEFT JOIN ".TBL_GCG." AS G
	  ON G.tour=T.id AND G.round=H.runda AND (G.player1=H.player1 OR G.player1=H.player2)
      WHERE H.player1='".$q."'
      ORDER BY T.name, H.runda";

$result = db_query($sql);

$sql_singleuser = "SELECT name_show FROM ".TBL_PLAYER." WHERE id = '" .$q."';";
$result_singleuser = db_query($sql_singleuser);
$user = db_fetch_assoc($result_singleuser);
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
if ($result)
while($row = db_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>".$row['name']."</td>";
    echo "<td>".$row['runda']."</td>";
    if ($row['host']==null || $row['host'] == 1) {
        $order = array($user['name_show'], $q, $row['name_show'], $row['player2'], $row['result1'], $row['result2']);
    } else {
        $order = array($row['name_show'], $row['player2'], $user['name_show'], $q, $row['result2'], $row['result1']);
    }
    echo "<td>".$order[0]."</td>";
    echo "<td>".$order[2]."</td>";
    echo "<td>".$order[4]."</td>";
    echo "<td>".$order[5]."</td>";
    echo "<td>";

    $temp_fname = $row['turniej'].'_'.$row['runda'].'_'.$order[1].'_'.$order[3].'.gcg';
    
    if ($row['round']!=null) {
        echo '<a href=board.php?turniej='.$row['turniej'].'&runda=' .$row['runda'].'&p1='.$order[1].'&p2='.$order[3].'>[zapis]</a> ';
    }
    else {
        echo '<div class="fileUpload btn btn-primary">';
        echo '<span>Dodaj</span>';
        echo '<input type="file" class="upload" ';
        echo 'data-index='.$i.' data-turniej='.$row['turniej'].' data-runda=';
        echo $row['runda']." data-player1=".$order[1]." data-player2=".$order[3];
        echo " data-p1pts=".$order[4]." data-p2pts= ".$order[5];
        echo ' /></div>';
    }
    echo '</td>';
    echo "</tr>";
    $i += 1;
}
echo "</table>";
db_close();
?>
</body>
</html>