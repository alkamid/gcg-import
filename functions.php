<?php

function getFinalScore($gcg) {

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

    if ((mb_detect_encoding($myfile_utf, 'UTF-8', true) == 'UTF-8') === FALSE) {
        $converted = str_replace($in_chars, $out_chars, $inp);
    }
    else {
        $converted = $inp;
    }
    return $converted;
}

?>