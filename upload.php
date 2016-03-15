<?php

    if ( 0 < $_FILES['file']['error'] ) {
        echo 'Error: ' . $_FILES['file']['error'] . '<br>';
    }
    else if ($_FILES["file"]["size"] > 3000) {
        echo "Zbyt duży plik (ograniczenie do 3kb).";
    }
    else {
        $tmpName  = $_FILES['file']['tmp_name'];
        $fp      = fopen($tmpName, 'r');
        $content = fread($fp, filesize($tmpName));
        fclose($fp);

        include "config.php";
   
        $con = mysqli_connect($mysqlhost,$mysqluser, $mysqlpwd, $mysqldbname);

        if (!$con) {
            die('Could not connect: ' . mysqli_error($con));
        }
        $gcg = mysqli_real_escape_string($con, $content);
        $new_fname = $_POST['turniej'] . '_' . $_POST['runda'] . '_' . $_POST['player1'] . '_' . $_POST['player2'];
        //PFSTOURHH.gcg: NULL (no gcg file) / 1 (one player uploaded a file) / 2 (two players uploaded a file)
        $query = "UPDATE PFSTOURHH SET gcg = 1 WHERE turniej = " . $_POST['turniej'] . " AND runda = " .$_POST['runda'] . " AND player1= ". $_POST['player1'] ." AND player2= ". $_POST['player2'] .";";
        $move_success = move_uploaded_file($_FILES['file']['tmp_name'], 'upload/gcg/' . $new_fname . '.gcg');

        if ($move_success) {
            if (mysqli_query($con, $query)) {
                echo "Gra dodana pomyślnie";
            } else {
                echo "Błąd przy dodawaniu gry: " . mysqli_error($con);
            }
        }
        else {
            echo "Błąd przy dodawaniu gry (PHP)";
        }
        
    }

?>