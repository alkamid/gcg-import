<?php

    if ( 0 < $_FILES['file']['error'] ) {
        echo 'Error: ' . $_FILES['file']['error'] . '<br>';
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
        print_r(mysqli_real_escape_string($content));
        mysqli_set_charset($con, "utf8mb4");
        $gcg = mysqli_real_escape_string($con, $content);
        $query = "UPDATE PFSTOURHH SET gcg = '$gcg' WHERE turniej = " . $_POST['turniej'] . " AND runda = " .$_POST['runda'] . " AND player1= ". $_POST['player1'] .";";
        
        if (mysqli_query($con, $query)) {
            echo "Gra dodana pomyślnie";
        } else {
            echo "Błąd przy dodawaniu gry: " . mysqli_error($con);
        }
        
    }

?>