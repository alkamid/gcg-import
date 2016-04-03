<html>
<head>
    <title>zapis</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="bootstrap-buttons.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script>

$( document ).ready(function() {

$('.upload').on('change', function(event) {
    var file_data = $(event.target).prop('files')[0];
    var form_data = new FormData();                  
    form_data.append('file', file_data);
    form_data.append('turniej', $(event.target).attr('data-turniej'));
    form_data.append('runda', $(event.target).attr('data-runda'));
    form_data.append('player1', $(event.target).attr('data-player1'));
    form_data.append('player2', $(event.target).attr('data-player2'));
    form_data.append('p1pts', $(event.target).attr('data-p1pts'));
    form_data.append('p2pts', $(event.target).attr('data-p2pts'));
    $.ajax({
            url: 'upload.php', // point to server-side PHP script 
     dataType: 'text',  // what to expect back from the PHP script, if anything
     cache: false,
     contentType: false,
     processData: false,
     data: form_data,                         
     type: 'post',
     success: function(php_script_response){
         var response = $.parseJSON(php_script_response);
         if ( response.status == 'error') {
             alert( response.errormsg );
         }
         else {
             alert('Zapis zaktualizowany.');
             window.location.reload(true);
         }
     }
     });
});
});
</script>
    <style type="text/css">
        table.zapis { width: 243px; font-size: 9px;}
        table.zapis td.gracz{ font-size: 14px; font-weight: bold; text-align: center; padding: 10px 5px; }
        table.zapis th, table.zapis td{ padding: 6px 2px; }
        table#plansza   {margin: 5px; text-align: center; border-spacing:0px;}
        table#plansza td    {width: 25px; height: 25px;  line-height: 25px; background:#3C8571;  border: 1px solid #23574B;}
        table#plansza th, table#plansza td:first-child  {width: 20px; height: 25px; line-height: 25px; background:#23574B; border: 1px solid #23574B; color:white; font-weight: normal; font-size:9px;}
        table#plansza td.word3    {background: #DB3920;}
        table#plansza td.word2   {background: #EFA284;}
        table#plansza td.letter3       {background: #4194E0;}
        table#plansza td.letter2  {background: #79B6E5;}
        table#plansza td.letter {background: #fcfac4;}
            .bottomSpan {bottom: 0; right: 0; position: relative; right: 0; font-size: 6px;}
div#gcg {font-family:monospace;}
        span.blank{color: red;}
        #worek{ text-align: left;   margin: 0 10px 20px 10px;}
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

include 'functions.php';

mb_internal_encoding("UTF-8");
error_reporting( E_ALL );

function showBoard($moves, $gcgtext) {
$board = array_fill(0, 15, array_fill(0, 15, 0));
$j =0;

$board_bonus['word3'] = array(array(0, 0), array(14,14), array(0, 14), array(14, 0), array(0,7),
                      array(7,0), array(14,7), array(7,14));
$board_bonus['word2'] =  array(array(7,7),
                               array(1,1), array(2,2), array(3,3), array(4,4), array (10, 10), array(11,11), array(12,12), array(13,13),
                               array(1,13), array(2,12), array(3,11), array(4,10), array(13,1), array(12,2), array(11,3), array(10,4));
$board_bonus['letter3'] = array(array(5,5), array(9,9), array(5,9), array(9,5),
                                array(5,1), array(1,5), array(9,1), array(1,9), array(5,13), array(13,5), array(9,13), array(13,9));
$board_bonus['letter2'] = array(array(3,0), array(0,3), array(14,3), array(3,14), array(11,0), array(11, 14), array(14, 11), array(0,11),
                                array(3,7), array(7,3), array(7,11), array(11,7),
                                array(2,6), array(2,8), array(6,2), array(8,2), array(12,6), array(12,8), array(6,12), array(8,12),
                                array(6,6), array(8,8), array(8,6), array(6,8));
$board_bonuses = array_fill(0, 15, array_fill(0, 15, 0));
foreach ($board_bonus as $k => $v) {
    foreach ($v as $t) {
        $board_bonuses[$t[0]][$t[1]] = $k;
    }
}

$length = count($moves);
for ($k = 0; $k < $length; $k++) {
    $mv = explode(' ', $moves[$k]);
    if ($k < $length - 1) {
        $next_mv = explode(' ', $moves[$k+1]);
        if ($next_mv[2] == '--') {
            continue;
        }
    }

    if ($mv[2][0] != '-') {
        if (ord(substr($mv[2], -1)) > 64 && ord(substr($mv[2], -1)) < 80) {
            $start_row = intval(substr($mv[2], 0, -1))-1;
            $end_row = $start_row;
            $start_col = ord(substr($mv[2], -1)) - 65;
            $end_col = $start_col + mb_strlen($mv[3])-1;
        }
        else {
            $start_row = intval(substr($mv[2], 1))-1;
            $end_row = $start_row + mb_strlen($mv[3])-1;
            $start_col = ord(substr($mv[2], 0, 1)) - 65;
            $end_col = $start_col;
        }            
        
        $i = 0;
        for ($row = $start_row; $row <= $end_row; $row++) {
            for ($col = $start_col; $col <= $end_col; $col++) {
                if (mb_substr($mv[3], $i, 1) != '.') {
                    $board[$row][$col] = mb_substr($mv[3], $i, 1);
                }
                $i += 1;
            }
            }
    }

}
    print '<div id="board">
    </table>
    <table id="plansza" class="onleft">
        <tr>
            <th>&nbsp;</th>
';

    for ($i = 1; $i < 16; ++$i) {
        print "<th>$i</th>";
    }

    print '<th>&nbsp;</th></tr>';

for ($row = 0; $row < 15; $row++) {
    $output .= '<tr><td class="aligncenter">' . chr(65 + $row) . '</td>';

    for ($col = 0; $col < 15; $col++) {

        if ($board[$row][$col] !== 0) {
            $output .= '<td class="letter">' . $board[$row][$col] . '</td>';
        }
        else {
            $output .= '<td class="' . $board_bonuses[$row][$col] . '"></td>';
        }
    }
    $output .= '<th>&nbsp;</th></tr>';
}


$output .= '<tr>';
for ($i = 0; $i < 17; $i++) {
    $output .= '<th>&nbsp;</th>';
}
$output .= '</tr></table></div>';
print $output;

print '[<a href="download.php?turniej=' . $_GET['turniej'] . '&runda=' . $_GET['runda'] . '&p1=' . $_GET['p1'] . '">ściągnij zapis</a>]<br /><br />';

$gcgprint = '<div id="gcg">';
$gcgprint .= nl2br($gcgtext);

fclose($myfile);

$gcgprint .='</div>';
print $gcgprint;


session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['user']="";
    $_SESSION['pass']="";
}

if (isset($_POST["user"])) {	
    $_SESSION['user']=hash('sha256', $_POST['user']);
    $_SESSION['pass']=hash('sha256', $_POST['pass']);
}

if($_SESSION['user'] == "436de63860c12db3e5c43dd39932e7fa83c406fd92dc2eb555637f9f94c4d616"
	&& $_SESSION['pass'] == "33a3422fdb68d68e0887c62217c93b9de3b01752ecda9ea996b4105b169ccc36")
	{
        $gcgscores = getFinalScore(explode(PHP_EOL, $gcgtext));
        if ($gcgscores != -1) {

            echo '<div class="fileUpload btn btn-primary">';
            echo '<span>Zaktualizuj</span>';
            echo '<input type="file" class="upload" ';
            echo ' data-turniej=' . $_GET['turniej'] . ' data-runda=';
            echo $_GET['runda'] . " data-player1=" . $_GET['p1'] . " data-player2=" . $_GET['p2'];
            echo " data-p1pts=" . $gcgscores['p1'] . " data-p2pts= " . $gcgscores['p2'];
            echo ' /></div>';
        }
        else {
            print_r($gcgscores);
        }
    }
else {
		?>
    <br /><br />Zaloguj się, by aktualizować zapisy:
                <form method="POST" action="">
					login: <input type="text" name="user"><br/>
					hasło: <input type="password" name="pass"><br/>
					<input type="submit" name="submit" value="zaloguj">
				</form>
			</body>
			</html>	
		<?php
}

  
}


function generateMovesTable($gcg) {
    $all_lines = explode(PHP_EOL, $gcg);
    return array_slice($all_lines, 2);
}


include 'config.php';
$con = mysqli_connect($mysqlhost, $mysqluser, $mysqlpwd, $mysqldbname);
$query = 'SELECT gcg FROM PFSTOURHH WHERE turniej = ' . $_GET['turniej'] . ' AND runda = ' . $_GET['runda'] . ' AND player1 = ' . $_GET['p1'] . ';';
mysqli_set_charset($con, 'utf8');
$result = mysqli_query($con, $query);
if ($result) {
    $gcgtext = mysqli_fetch_array($result)[0];
    //$moves_fname = 'upload/gcg/' . $_GET['turniej'] . '_' . $_GET['runda'] . '_' . $_GET['p1'] . '_' . $_GET['p2'] . '.gcg';
    $mymoves = generateMovesTable($gcgtext);
    showBoard($mymoves, $gcgtext);
    }
?>

</body>
</html>