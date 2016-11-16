<?php

include "functions.php";

function validateGCG($gcg_file, $p1, $p2) {
    $gcg = file_get_contents($gcg_file);
    $gcgscores = getFinalScore($gcg);
    $encoding_issues = checkMovesEncoding($gcg);

    if (checkPlayers($gcg_file) === false) {
        return array(FALSE, 'Nieprawidłowy plik .gcg — sprawdź nazwy graczy w nagłówku i przy ruchach');
    }
    if ($encoding_issues == -1) {
        return array(FALSE, 'Nieprawidłowe kodowanie pliku — sprawdź czy zapis nie zawiera znaków zapytania');
            }
    
    if ($gcgscores === -1) {
        return array(FALSE, 'Nie znaleziono wyniku — prawdopodobnie nieprawidłowy plik .gcg' . $p1 . $p2);
    }
    else {
        if ((abs((int) $gcgscores['p1'] - (int) $p1) < 30 && abs((int) $gcgscores['p2'] - (int) $p2) < 30) || (abs((int) $gcgscores['p1'] - (int) $p2) < 30 && abs((int) $gcgscores['p2'] - (int) $p1) < 30)) {
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

        if ($_POST['update'] == 1) {
            $query = "SELECT data FROM ".TBL_GCG." WHERE tour=".$_POST['turniej']." AND round=".$_POST['runda']." AND player1=".$play1.';';
            $result = db_query($query);
            if ($result) {
                $oldgcgtext = db_fetch_row($result)[0];
            }
            $myfile_utf = mergeGCG($myfile_utf, $oldgcgtext);
        }
        $query1 = "INSERT INTO ".TBL_GCG." (tour,round,player1,data)"
			." VALUES (".$_POST['turniej'].",".$_POST['runda'].",".$play1
			.",'".mysql_real_escape_string($myfile_utf)."')"
            ." ON DUPLICATE KEY UPDATE data='".mysql_real_escape_string($myfile_utf)."'";
        
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