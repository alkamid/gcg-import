<?php

function validateGCG($gcg_file, $p1, $p2) {
    $gcg = file($gcg_file, FILE_IGNORE_NEW_LINES);
    foreach(array_reverse($gcg) as $line) {
        if (substr($line, 0, 1) == '>') {
            $lsp = explode(' ', $line);
           
            if (!isset($playerA) && substr($lsp[2], 0, 1) == '(') {
                $letters_value = intval(substr($lsp[3], 1)) / 2;
                $playerA = array($lsp[0], intval(end($lsp))-$letters_value);
            }
            elseif ($lsp[0] != $playerA[0]) {
                $playerB = array($lsp[0], intval(end($lsp))-$letters_value);
                if (($playerA[1] == $p1 && $playerB[1] == $p2) || ($playerA[1] == $p2 && $playerB[1])) {
                    return 1;
                }
                else {
                    return array(FALSE, 'Wynik się nie zgadza (.gcg: ' . $p1 . '–' . $p2 . ', PFS: ' . $playerA[1] . '–' . $playerB[1] . ').');
                }
            }
        }
    }
}


//http://stackoverflow.com/questions/9676084/how-do-i-return-a-proper-success-error-message-for-jquery-ajax-using-php
header('Content-type: application/json');
$response_array['status'] = 'error';

$validation = validateGCG($_FILES['file']['tmp_name'], $_POST['p1pts'], $_POST['p2pts']);

    if ( 0 < $_FILES['file']['error'] ) {
        $response_array['errormsg'] = $_FILES['file']['error'];
    }
    elseif ($_FILES["file"]["size"] > 3000) {
        $response_array['errormsg'] = 'Zbyt duży plik (ograniczenie do 3kB)';
    }
    elseif ($validation != 1) {
        
        $response_array['errormsg'] = $validation[1];
    }
    else {
        include "config.php";
   
        $con = mysqli_connect($mysqlhost,$mysqluser, $mysqlpwd, $mysqldbname);

        if (!$con) {
            die('Could not connect: ' . mysqli_error($con));
            $response_array['status'] = 'error';
        }
        $new_fname = $_POST['turniej'] . '_' . $_POST['runda'] . '_' . $_POST['player1'] . '_' . $_POST['player2'];
        //PFSTOURHH.gcg: NULL (no gcg file) / 1 (one player uploaded a file) / 2 (two players uploaded a file)
        $query1 = "UPDATE PFSTOURHH SET gcg = 1 WHERE turniej = " . $_POST['turniej'] . " AND runda = " .$_POST['runda'] . " AND player1= ". $_POST['player1'] ." AND player2= ". $_POST['player2'] .";";
        $query2 = "UPDATE PFSTOURHH SET gcg = 1 WHERE turniej = " . $_POST['turniej'] . " AND runda = " .$_POST['runda'] . " AND player2= ". $_POST['player1'] ." AND player1= ". $_POST['player2'] .";";
        $move_success = move_uploaded_file($_FILES['file']['tmp_name'], 'upload/gcg/' . $new_fname . '.gcg');

        if ($move_success) {
            if (mysqli_query($con, $query1) && mysqli_query($con, $query2)) {
                $response_array['status'] = 'success';
            } else {
                $response_array['status'] = 'error';
                $response_array['errormsg'] = 'Błąd przy dodawaniu gry (PHP)' . mysqli_error($con);

            }
        }
        else {
            $response_array['status'] = 'error';
            $response_array['errormsg'] = 'Błąd przy dodawaniu gry (PHP)';
        }
        
    }
echo json_encode($response_array);

?>