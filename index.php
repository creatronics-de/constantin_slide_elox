<?php
namespace schieberaetsel;

require_once 'Tree.php';
require_once 'Board.php';
require_once 'Tile.php';

//create Board
$board = new Board(4,4);

include "board_1.php"; //32
//include "board_2.php"; //56
//include "board_3.php"; //74
//include "board_4.php"; //118
//include "board_5.php"; //128
//include "board_6.php"; //165
//include "board_7.php"; //165
//include "board_8.php"; //191


/* everything else is calculated here*/
$GY = new Tile('GR', $YG->x, $YG->y+1, $YG);
$board->addTiles([$YG, $GY]);
$BG = new Tile('BL', $GB->x, $GB->y+1, $GB);
$board->addTiles([$GB, $BG]);
$YO = new Tile('YE', $OY->x+1, $OY->y, $OY);
$board->addTiles([$OY, $YO]);
$board->addTiles($R);
$board->addTiles($O);
$VB = new Tile('VI', $BV->x+1, $BV->y, $BV);
$board->addTiles([$BV,$VB]);
$RV=new Tile('RO',$VR->x,$VR->y+1,$VR);
$board->addTiles([$VR,$RV]);
$S2=new Tile('si',$S1->x+1,$S1->y,$S1);
$board->addTiles([$S1,$S2]);


//Zielpositionen hinzufügen
$R->addGoal(1,1);
$OY->addGoal(2,1);
$GB->addGoal(4,1);
$VR->addGoal(4,3);
$O->addGoal(1,2);
$YG->addGoal(1,3);
$BV->addGoal(2,4);

$res = $board->solve();

$board->showSolution($res);
echo "Done";


