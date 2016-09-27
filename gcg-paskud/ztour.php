<?php require_once("../head0.php");?>
<?php

$id=intval($_GET['p']);
$tid=intval($_GET['t']);
if (isset($_GET['s'])) {
	$sort=intval($_GET['s']);
} else {
	$sort = 1;
}

require_once("../tablefun.php");

$dTableArr = new DispTableArr("ztour_".$tid."_".$id, 2, array(
new DispTable(),
new DispTable(false, $sort)
));

if (!$dTableArr->load()) {
	require_once("../system.php");
	require_once("../queries.php");
	$arrheads=array(
		"<th width=\"2%\">#1#Runda#2#",	
		"<th width=\"5%\">#1#Stół#2#");
	if (SHOWHOST) $arrheads[]="<th width=\"1%\">*";
	$arrheads[]="<th width=\"1%\">+";
	$arrheads[]="<th width=\"25%\">Przeciwnik";
	$arrheads[]="<th width=\"7%\">#1#Rank#2#";
	$arrheads[]="<th width=\"10%\">#1#Małych#2#";
	$arrheads[]="<th width=\"10%\">#1#Straconych#2#";
	$arrheads[]="<th width=\"10%\">#1#Suma małych#2#";
	$arrheads[]="<th width=\"5%\">#1#Skalp#2#";
	$arrheads[]="<th width=\"3%\">Zapis";
	$arrtypes=array('i','i');
	if (SHOWHOST) $arrtypes[]='s';
	$arrtypes[]='s';
	$arrtypes[]='s';
	$arrtypes[]='f';
	$arrtypes[]='i';
	$arrtypes[]='i';
	$arrtypes[]='i';
	$arrtypes[]='i';
	$arrtypes[]='s';
	$arrsort=array(1,2);
	if (SHOWHOST) $arrsort[]=0;
	$arrsort[]=0;
	$arrsort[]=0;
	$arrsort[]=-5;
	$arrsort[]=-6;
	$arrsort[]=-7;
	$arrsort[]=-8;
	$arrsort[]=-9;
	$arrsort[]=0;
	$arrtit=array(false,"Nr stołu, na którym rozgrywano grę");
	if (SHOWHOST) $arrtit[]=false;
	$arrtit[]=false;
	$arrtit[]=false;
	$arrtit[]="Ranking przeciwnika przed turniejem";
	$arrtit[]="Małych punktów";
	$arrtit[]="Małych punktów przeciwnika";
	$arrtit[]="Suma małych gracza i przeciwnika";
	$arrtit[]="Zdobyty skalp";
	$arrtit[]=false;
	$dTableArr->aDispTable[1]->setHeads($arrheads);
	$dTableArr->aDispTable[1]->setTypes($arrtypes);
	$dTableArr->aDispTable[1]->setDefaultSort($arrsort);
	$dTableArr->aDispTable[1]->setHeadTitles($arrtit);
	db_open();

	$res = db_query("SELECT * FROM ".TBL_TOURS." WHERE id=".$tid);
	$turrow = db_fetch_assoc($res);
	$turid=$turrow["id"];
	$res = db_query("SELECT * FROM ".TBL_TOURWYN." WHERE player=".$id." AND turniej=".$tid);
	$wynrow = db_fetch_assoc($res);


	{
		$tlink = getTurniejKLinkFromId($tid);
		$dTableArr->addAdditional("<title>%s: podsumowanie turnieju %s</title>"
		."</head><body><zs>%s: podsumowanie turnieju %s</zs>",
		array(getPlayerNameShow($id),trim($turrow['name']),getPlayerLink($id),getTurniejKLinkFromARow($turrow)));
	}
	$nextLink=false;
	$nexttur = getPrevZturniej($tid,$id);
	if ($nexttur!==false) {
		$nextLink="<a href=\"ztour.php?p=".$id."&t=".$nexttur."&s=".$sort."\">Poprzedni turniej</a><br>";
	}
	$nexttur = getNextZturniej($tid,$id);
	if ($nexttur!==false) {
		$nextLink.="<a href=\"ztour.php?p=".$id."&t=".$nexttur."&s=".$sort."\">Następny turniej</a><br>";
	}
	if ($nextLink!==false) {
		$dTableArr->addAdditional($nextLink);
	}
	$prevtid=getPrevTurniej($tid);
	$res=db_query("SELECT runda,stol,player2,result1,result2,ranko,host FROM ".TBL_TOURHH." WHERE player1=".$id
	." AND turniej=".$tid." ORDER BY runda");
	$sumres1=0;$sumres2=0;$sumskapl=0;$games=0;$nwgames=0;$sumrank=0;$sumstol=0;$dpkt=0;$sumhost=0;
	while(false!==($row=db_fetch_row($res))) {
		if ($row[3]==$row[4]) {
			$skalp=$row[5];
			$plus='=';
			$dpkt+=0.5;
		} elseif($row[3]<$row[4]) {
			$skalp=$row[5]-50;
			$plus='-';
		} else {
			$skalp=$row[5]+50;
			$plus='+';
			++$dpkt;
		}
		if ($row[6]==1) {
			++$sumhost;
			$host="*";
		} else {
			$host=".";
		}
		$arrrow=array($row[0],$row[1]);
		if (SHOWHOST) $arrrow[]=$host;
		$arrrow[]=$plus;
		$arrrow[]=getTourPlayerLink($row[2],$turid);
		$arrrow[]=$prevtid===false?$row[5]:getRank($row[2],$prevtid);
		$arrrow[]=$row[3];
		$arrrow[]=$row[4];
		$arrrow[]=$row[3]+$row[4];
		$arrrow[]=$skalp;
		$dTableArr->aDispTable[1]->addRow($arrrow);
		++$games;
		if (!isWalkower($row[3],$row[4])) {
			++$nwgames;
			$sumres1+=$row[3];
			$sumres2+=$row[4];
		}
		$sumskapl+=$skalp;
		$sumrank+=$row[5];
		$sumstol+=$row[1];
	}
	if ($games>0) {
		$arrsum=array();
		if (SHOWHOST) $arrsum[]=($sumhost==0?'.':$sumhost);
		$arrsum[]=round($dpkt,1);
		$arrsum[]=$sumrank;
		$arrsum[]=$sumres1;
		$arrsum[]=$sumres2;
		$arrsum[]=$sumres1+$sumres2;
		$arrsum[]=$sumskapl;
		$dTableArr->aDispTable[1]->addSummary("<tr id=\"sum\"><td>-<td>-".(SHOWHOST?"<td>%d":"")."<td>%.01f<td>SUMA<td>%d<td>%d<td>%d<td>%d<td>%d",$arrsum);
		$arravg=array(round($sumstol/$games,1),round(1.0*$sumrank/$games,1),dziel($sumres1,$nwgames),dziel($sumres2,$nwgames),dziel($sumres1+$sumres2,$nwgames),round(1.0*$sumskapl/$games,1));
		$dTableArr->aDispTable[1]->addSummary("<tr id=\"sum\"><td>-<td>%.01f<td>-".(SHOWHOST?"<td>-":"")."<td>ŚREDNIA<td>%.01f<td>%.01f<td>%.01f<td>%.01f<td>%.01f",$arravg);
	}
	

	{
		$miej=$wynrow['place'];
		if ($miej<=0) $miej='-';
		$dTableArr->aDispTable[0]->addRow(array("Miejsce w turnieju / uczestników", 
											"<zh>".$miej."</zh>", "<zh>".$turrow['players']."</zh>"));
		if ($games>0) 									
		$dTableArr->aDispTable[0]->addRow(array("Rank z turnieju / średni rank przeciwników", 
										"<zh>".round(1.0*$sumskapl/$games,1)."</zh>", round(1.0*$sumrank/$games,1)
										));
	}
	{
		$sumy[0] = sumaMalychTourHH("turniej=".$tid." AND result1>result2",$id);
		$sumy[1] = sumaMalychTourHH("turniej=".$tid." AND result1<result2",$id);
		$sumy[2] = sumaMalychTourHH("turniej=".$tid,$id);
		$dTableArr->aDispTable[0]->addRow(array("Średnia małych punktów / przeciwnicy", 
			dzielf1($sumy[2][0],$sumy[2][2]), dzielf1($sumy[2][1],$sumy[2][2])
		));
		$dTableArr->aDispTable[0]->addRow(array("Średnia małych w wygranych / przeciwnik", 
			dzielf1($sumy[0][0],$sumy[0][2]), dzielf1($sumy[0][1],$sumy[0][2])
		));
		$dTableArr->aDispTable[0]->addRow(array("Średnia małych w przegranych / przeciwnik", 
			dzielf1($sumy[1][0],$sumy[1][2]), dzielf1($sumy[1][1],$sumy[1][2])
		));
		$dTableArr->aDispTable[0]->addRow(array("Średnia suma / różnica", 
			dzielf1($sumy[2][0]+$sumy[2][1],$sumy[2][2]), dzielf1($sumy[2][0]-$sumy[2][1],$sumy[2][2])
		));
		$dTableArr->aDispTable[0]->addRow(array("Średnia różnica w wygranych / przegranych", 
			dzielf1($sumy[0][0]-$sumy[0][1],$sumy[0][2]), dzielf1($sumy[1][1]-$sumy[1][0],$sumy[1][2])
		));
		if (SHOWMASTERS) {
			$masters=$wynrow['masters'];
			if($masters>0) {
				$dTableArr->aDispTable[0]->addRow(array("Do klasyfikacji Masters", 
					$masters,"-"
				));
			}
		}
	}
	
	
//no cache		$dTableArr->save();
	db_close();
}
if ($sort!=1) {
	$dTableArr->aDispTable[1]->sortRows(array($sort,1));
}
//todo
print($dTableArr->additional[0]);
print("<br><table id=\"nob\"width=\"100%\"><tr><td id=\"nobl\">");
print("<table border=\"1\" width=\"70%\"><tr><th id=\"nob\" width=\"64%\"><th id=\"nob\" width=\"18%\"><th id=\"nob\" width=\"18%\">");
$dTableArr->aDispTable[0]->printRows("<tr><td>%s<td>%s<td>%s");
print("</table><td id=\"nobrt\">");
if (isset($dTableArr->additional[1])) print($dTableArr->additional[1]);
//printf("<a href=\"ztours.php?p=%d\">Lista turniejów gracza</a></center>",$id);
print("</table><br><center><table border=\"1\" width=\"99%%\"><tr>");
$dTableArr->aDispTable[1]->printHeader("<a href=\"ztour.php?p=".$id."&t=".$tid."&s=%d\"%s>","</a>");
$dTableArr->aDispTable[1]->printRows("<tr><td>%d<td>%d".(SHOWHOST?"<td>%s":"")."<td>%s<td>%s<td>%.02f<td>%d<td>%d<td>%d<td>%d");
print("</table><br><center><br>");

?>
<br><a href="index.php" target="_top">Strona główna</a></center>
<?php insertVisitorStats($id); ?>
</body></html>
