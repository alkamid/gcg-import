<?php

require_once "../system.php";
function showGameInfo($tour, $player1, $player2) {
    db_open();
    $query = "select P1.name_show as p1name, P2.name_show as p2name, T.name as tour, HH.runda, HH.host
              from " . TBL_TOURHH ." as HH
              inner join " . TBL_PLAYER . " as P1 on P1.id = HH.player1
              inner join " . TBL_PLAYER . " as P2 on P2.id = HH.player2
              inner join " . TBL_TOURS . " as T on T.id = HH.turniej
              WHERE T.id=" .$tour. " AND HH.player1=" .$player1 . " AND HH.player2=" . $player2 . ";";
    $result = db_query($query);
    if ($result) {
        $res = db_fetch_row($result);
        $output = $res[2] . ', Runda ' . $res[3] . ': ';
        if ($res[4] == 1) {
            $output .= $res[0] . ' — ' . $res[1];
        }
        else {
            $output .= $res[1] . ' — ' . $res[0];
        }
        return $output;
    }
    else {
        print db_error();
    }
}

function nextPrevGame($tour, $player1, $player2) {

}

function checkPlayers($gcgtext) {
    $gcg = preg_split("/\\r\\n|\\r|\\n/", $gcgtext);
    
    foreach ($gcg as $i => $line) {
        $lsp = explode(' ', $line);
        
        if ($i === 0) {
            $p1 = $lsp[1];
        }
        elseif ($i === 1) {
            $p2 = $lsp[1];
        }

        elseif (substr($line, 0, 1) === '>') {
            $current_player = substr($lsp[0], 1, -1);
            if ($current_player !== $p1 && $current_player !== $p2) {
                return false;
            }
        }
    }
    return true;
}

function uploadButton($tour_id, $round, $p1, $p2, $p1pts, $p2pts) {

    $uplbutton = '<div class="fileUpload btn btn-primary">
            <span>[+]</span>
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

function countLowercase($string) {
    return (int) preg_match_all('/\p{Ll}/u', $string);
}

function gcgToTable($gcgtext) {
    $gcg = preg_split("/\\r\\n|\\r|\\n/", $gcgtext);

    $table = "<table class='gcg'>";

    $stats['p1']['bingos'] = 0;
    $stats['p2']['bingos'] = 0;
    $stats['p1']['blanks'] = 0;
    $stats['p2']['blanks'] = 0;
    $stats['p1']['chall'] = 0;
    $stats['p2']['chall'] = 0;
    $stats['p1']['exch'] = 0;
    $stats['p2']['exch'] = 0;

    $limit = sizeof($gcg);
    $i = 0;
    while ($i <= $limit) {
        $line = $gcg[$i];
        $lsp = explode(' ', $line);
        
        if (substr($line, 0, 8) == '#player1') {
            $players['p1'] = $lsp[1];
        }
        elseif (substr($line, 0, 8) == '#player2') {
            $players['p2'] = $lsp[1];
        }
        elseif (substr($line, 0, 1) == '>') {

            $current_player = substr($lsp[0], 1, -1);

            $rack = $lsp[1];
            $pos = $lsp[2];
            $move = $lsp[3];
            $pts = $lsp[4];
            $sum = $lsp[5];

            if ($i < $limit-1 && strpos($gcg[$i+1], '--') !== false) {
                $stats = addStat($current_player, $players['p1'], $players['p2'], $stats, 'chall', 1);
                $move = 'str. (' . $lsp[3] . ')';
                $pts = '+0';
                $sum = intval($lsp[5]) - intval($lsp[4]);
                $i += 1;
            }
            elseif (countLowercase($lsp[3]) > 0) {
                $stats = addStat($current_player, $players['p1'], $players['p2'], $stats, 'blanks', countLowercase($lsp[3]));
            }

            if (substr($lsp[2], 0, 1) == '-') {
                if ($lsp[2] === '-' && $lsp[3] === ' ') {
                    $move = 'pas' ;
                    $pts = $lsp[4];
                    $sum = $lsp[5];
                }
                else {
                    $move = 'wym. ' . substr($lsp[2], 1);
                    $stats = addStat($current_player, $players['p1'], $players['p2'], $stats, 'exch', 1);
                    $pts = $lsp[3];
                    $sum = $lsp[4];
                }
                $pos = '';
            }
            elseif (substr($lsp[2], 0, 1) == '(') {
                $rack = $lsp[2];
                $pos = '';
                $move = '';
                if (substr($lsp[3], 0, 1) == '+') {
                    $bonus = intval(substr($lsp[3], 1))/2;
                    $pts = '+' . $bonus;
                    $sum = intval($lsp[4]) - $bonus;
                }
                else {
                    $pts = $lsp[3];
                    $sum = $lsp[4];
                }
            }

            if (isBingo($move)) {
                $stats = addStat($current_player, $players['p1'], $players['p2'], $stats, 'bingos', 1);
            }

            $table .= moveTableLine($current_player, $rack, $pos, $move, $pts, $sum);

            if ($i == $limit -2 && ($stats['p1']['blanks'] + $stats['p2']['blanks'] < 2)) {
                $stats = addStat($current_player, $players['p1'], $players['p2'], $stats, 'blanks', substr_count($rack, '?'));
            }
        }
        elseif (substr($line, 0, 1) == '#' && substr($line, 0, 7) !== '#player') {
            $table .= '<tr><td colspan=6>' . $line . '</td></tr>';
        }
        
        $i += 1;   
    }
    $last = getFinalScore($gcgtext);
    $table .= moveTableLine($last['p2name'], $last['rack'], '', '', '-' . $last['diff'], $last['p2']);
    $table .= "</table>";
    
    $output['table'] = $table;
    $output['stats'] = $stats;
    $output['players'] = $players;
    return $output;
}

function addStat($current_player, $p1, $p2, $stats, $key, $value) {
    if ($current_player == $p1) {
        $player_key = 'p1';
    }
    elseif ($current_player == $p2) {
        $player_key = 'p2';
    }

    $stats[$player_key][$key] += $value;
    return $stats;
}

function isBingo($move) {
    if (preg_match_all('/\p{L}/u', $move) == 7) {
        return true;
    }
    else {
        return false;
    }
}

function statsTable($stats, $players) {
    $table = '<table class="stats"><tr><th></th><th>'. $players['p1'] . '</th><th>'. $players['p2'] .'</th></tr>';
    $table .= '<tr><td>blanki</td><td class="statsint">' . nonzero($stats['p1']['blanks']) . '</td><td class="statsint">'. nonzero($stats['p2']['blanks']) . '</td></tr>';
    $table .= '<tr><td style="padding-right:5px">premie</td><td class="statsint">' . nonzero($stats['p1']['bingos']) . '</td><td class="statsint">'. nonzero($stats['p2']['bingos']) . '</td></tr>';
    $table .= '<tr><td>straty</td><td class="statsint">' . nonzero($stats['p1']['chall']) . '</td><td class="statsint">'. nonzero($stats['p2']['chall']) . '</td></tr>';
    $table .= '<tr><td style="padding-right:5px">wymiany</td><td class="statsint">' . nonzero($stats['p1']['exch']) . '</td><td class="statsint">'. nonzero($stats['p2']['exch']) . '</td></tr>';
    $table .= '</table>';
    return $table;
}

function nonzero($number) {
    if (intval($number) == 0) {
            return '';
        }
    else {
        return $number;
    }
}

function moveTableLine($name, $rack, $pos, $move, $pts, $sum) {

    $tr = "<tr>";
    $tr .= "<td>" . $name . ":</td>";    
    $tr .= "<td class='rack'>" . $rack . "</td>";
    $tr .= "<td class='position'>" . $pos . "</td>";
    $tr .= "<td>" . $move . "</td>";
    $tr .= "<td class='pts'>" . $pts . "</td>";
    $tr .= "<td class='ptssum'>" . $sum . "</td>";
    $tr .= "</tr>";
    return $tr;     
}


function checkMovesEncoding($gcgtext) {
    $gcg = preg_split("/\\r\\n|\\r|\\n/", $gcgtext);

    foreach($gcg as $line) {
        if (substr($line, 0, 1) == '>') {
            $lsp = explode(' ', $line);

            if (strpos($lsp[3], "?") !== false) {
                return -1;
            }
        }
    }
    return 0;
}


function getFinalScore($gcgtext) {
    $gcg = preg_split("/\\r\\n|\\r|\\n/", $gcgtext);
    foreach(array_reverse($gcg) as $line) {
        if (substr($line, 0, 1) == '>') {
            $lsp = explode(' ', $line);
            if (!isset($playerA) && ((substr($lsp[2], 0, 1) == '(' || substr($lsp[1], 0, 1) == '(') || $lsp[2] == '--')) {
                $letters_value = intval(substr($lsp[3], 1)) / 2;
                $playerA = array($lsp[0], intval(end($lsp))-$letters_value);
                $out['rack'] = $lsp[2];
            }
            elseif (isset($playerA) && $lsp[0] != $playerA[0]) {
                $playerB = array($lsp[0], intval(end($lsp))-$letters_value);
                $out['p1'] = $playerA[1];
                $out['p2'] = $playerB[1];
                $out['diff'] = $letters_value;
                $out['p1name'] = substr($playerA[0], 1, -1);
                $out['p2name'] = substr($playerB[0], 1, -1);
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

        if (($is_line_the_same == 1) && (mb_strlen($newline[1]) <= 7 && (mb_strlen($newline[1]) > mb_strlen($oldline[1])))) {
                $oldline[1] = $newline[1];
                $resultline = implode(' ', $oldline);
        }
        elseif (mb_strlen($new[$l]) > 2) {
            $resultline = $new[$l];
        }

        array_push($merged, $resultline);
    }

    return implode("\n", $merged);

}


?>