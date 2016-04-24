<?php

include "functions.php";

function validateGCG($gcg_file, $p1, $p2) {
    $gcg = file($gcg_file, FILE_IGNORE_NEW_LINES);
    $gcgscores = getFinalScore($gcg);
    
    if ($gcgscores === -1) {
        return array(FALSE, 'Nie znaleziono wyniku — prawdopodobnie nieprawidłowy plik .gcg');
    }
    else {
        if (($gcgscores['p1'] == $p1 && $gcgscores['p2'] == $p2) || ($gcgscores['p1'] == $p2 && $gcgscores['p2'] == $p1)) {
            return 1;
        }
        else {
            return array(FALSE, 'Wynik się nie zgadza (PFS: ' . $p1 . '–' . $p2 . ', .gcg: ' . $gcgscores['p1'] . '–' . $gcgscores['p2'] . ').');
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
        $myfile = fopen($_FILES['file']['tmp_name'], 'r') or die('Unable to open file!');
        mb_internal_encoding("UTF-8");
		require_once "../system.php";
		db_open();

        $myfile_utf = utf_convert(fread($myfile, filesize($_FILES['file']['tmp_name'])));
        fclose($myfile);

		if ($_POST['player1']<$_POST['player2']) {
			$play1 = $_POST['player1'];
		} else {
			$play1 = $_POST['player2'];
		}
        $query1 = "INSERT INTO ".TBL_GCG." (tour,round,player1,data)"
			." VALUES (".$_POST['turniej'].",".$_POST['runda'].",".$play1
			.",'".mysql_real_escape_string($myfile_utf)."')";
        
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