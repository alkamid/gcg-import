<?php

function uploadButton($tour_id, $round, $p1, $p2, $p1pts, $p2pts) {

    $uplbutton = '<div class="fileUpload btn btn-primary">
            <span>Dodaj</span>
            <input type="file" class="upload" 
            data-turniej=' . $tour_id .
            ' data-runda=' . $round .
            ' data-player1=' . $p1 .
            ' data-player2=' . $p2 .
            ' data-p1pts=' . $p1pts .
            ' data-p2pts= ' . $p2pts .
            ' /></div>';
    return $uplbutton;

}

function getFinalScore($gcgtext) {
    $gcg = preg_split("/\\r\\n|\\r|\\n/", $gcgtext);

    foreach(array_reverse($gcg) as $line) {
        if (substr($line, 0, 1) == '>') {
            $lsp = explode(' ', $line);

            if (!isset($playerA) && substr($lsp[2], 0, 1) == '(') {
                $letters_value = intval(substr($lsp[3], 1)) / 2;
                $playerA = array($lsp[0], intval(end($lsp))-$letters_value);
            }
            elseif (isset($playerA) && $lsp[0] != $playerA[0]) {
                $playerB = array($lsp[0], intval(end($lsp))-$letters_value);
                $out['p1'] = $playerA[1];
                $out['p2'] = $playerB[1];
                return $out;
            }
        }
    }
        return -1;
}

function utf_convert($inp) {
    $in_chars = $out_chars = array();
         
    $in_chars[] = "\245";
    $out_chars[] = "Ą";
    $in_chars[] = "\271";
    $out_chars[] = "ą";
    $in_chars[] = "\306";
    $out_chars[] = "Ć";
    $in_chars[] = "\346";
    $out_chars[] = "ć";
    $in_chars[] = "\217";
    $out_chars[] = "Ź";
    $in_chars[] = "\237";
    $out_chars[] = "ź";
    $in_chars[] = "\312";
    $out_chars[] = "Ę";
    $in_chars[] = "\352";
    $out_chars[] = "ę";
    $in_chars[] = "\257";
    $out_chars[] = "Ż";
    $in_chars[] = "\277";
    $out_chars[] = "ż";
    $in_chars[] = "\243";
    $out_chars[] = "Ł";
    $in_chars[] = "\263";
    $out_chars[] = "ł";
    $in_chars[] = "\321";
    $out_chars[] = "Ń";
    $in_chars[] = "\361";
    $out_chars[] = "ń";
    $in_chars[] = "\214";
    $out_chars[] = "Ś";
    $in_chars[] = "\234";
    $out_chars[] = "ś";
    $in_chars[] = "\323";
    $out_chars[] = "Ó";
    $in_chars[] = "\363";
    $out_chars[] = "ó";

    if ((mb_detect_encoding($inp, 'UTF-8', true) == 'UTF-8') === FALSE) {
        $converted = str_replace($in_chars, $out_chars, $inp);
    }
    else {
        $converted = $inp;
    }

    return $converted;
}

function mergeGCG($newfile, $oldfile) {

    $new = preg_split("/\\r\\n|\\r|\\n/", $newfile);
    $old = preg_split("/\\r\\n|\\r|\\n/", $oldfile);

    $i = 0;
    $j = 0;
    foreach($old as $line_old) {
        if ($line_old[0] == '>') {
            $old_first_line = explode(' ', $line_old);
            break;
        }
        $i++;
    }
    foreach($new as $line_new) {
        if ($line_new[0] == '>') {
            $new_first_line = explode(' ', $line_new);
            break;
        }
        $j++;
    }

    $merged = array_slice($old, 0, $i);
    
    for ($k = $i, $l = $j; $k<=count($old), $l<=count($new) ; $k++, $l++) {
        $newline = explode(' ', $new[$l]);
        $oldline = explode(' ', $old[$k]);
        $resultline = '';
        
        $is_line_the_same = 1;
        for ($m = 2; $m <= 5; $m++) {
            if ($newline[$m] != $oldline[$m]) {
                $is_line_the_same = 0;
            }
        }

        if (($is_line_the_same == 1) && (strlen($newline[1]) <= 7 && (strlen($newline[1]) > strlen($oldline[1])))) {
                $oldline[1] = $newline[1];
                $resultline = implode(' ', $oldline);
        }
        elseif (strlen($new[$l]) > 2) {
            $resultline = $new[$l];
        }

        array_push($merged, $resultline);
    }

    return implode("\n", $merged);

}


?>