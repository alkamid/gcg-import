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
            elseif (isset($playerA) && $lsp[0] != $playerA[0]) {
                $playerB = array($lsp[0], intval(end($lsp))-$letters_value);
                if (($playerA[1] == $p1 && $playerB[1] == $p2) || ($playerA[1] == $p2 && $playerB[1])) {
                    return 1;
                }
                else {
                    return array(FALSE, 'Wynik się nie zgadza (.gcg: '.$p1.'–'.$p2.', PFS: '.$playerA[1].'–'.$playerB[1].').');
                }
            }
        }
    }
    return array(FALSE, 'Nie znaleziono wyniku — prawdopodobnie nieprawidłowy plik .gcg');
}


//http://stackoverflow.com/questions/9676084/how-do-i-return-a-proper-success-error-message-for-jquery-ajax-using-php
header('Content-type: application/json');
$response_array['status'] = 'error';

$validation = validateGCG($_FILES['file']['tmp_name'], $_POST['p1pts'], $_POST['p2pts']);
	return 1;
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
        $myfile = fopen($_FILES['file']['tmp_name'], 'r') or die('Unable to open file!');
        mb_internal_encoding("UTF-8");
        include "functions.php";
		require_once "../system.php";
		db_open();

        $myfile_utf = utf_convert(fread($myfile, filesize($_FILES['file']['tmp_name'])));
        fclose($myfile);

        $new_fname = $_POST['turniej'].'_'.$_POST['runda'].'_'.$_POST['player1'].'_'.$_POST['player2'];
        //PFSTOURHH.gcg: NULL (no gcg file) / 1 (one player uploaded a file) / 2 (two players uploaded a file)
		if ($_POST['player1']<$_POST['player2']) {
			$play1 = $_POST['player1'];
		} else {
			$play1 = $_POST['player2'];
		}
        $query1 = "INSERT INTO ".TBL_GCG." (tour,round,player1,data)"
			." VALUES (".$_POST['turniej'].",".$_POST['runda'].",".$play1
			.",'".mysql_real_escape_string($myfile_utf)."')";
        
        //$move_success = move_uploaded_file($_FILES['file']['tmp_name'], 'upload/gcg/'.$new_fname.'.gcg');

        //if ($move_success) {
        if (db_query($query1)) {
            $response_array['status'] = 'success';
        } else {
            $response_array['status'] = 'error';
            $response_array['errormsg'] = 'Błąd przy dodawaniu gry (MySQL)'.db_error();
        }
		db_close();	
    }
echo json_encode($response_array);

?>