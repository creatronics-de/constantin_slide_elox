<?php

namespace schieberaetsel;

use function PHPUnit\Framework\isInstanceOf;

class Board {
  /**
   * @var Tile[]
   */
  protected int $h;
  protected int $w;
  /**
   * @var Tile[][]|mixed|null
   */
  private mixed $board;
  private int $step = 0;
  private Tree $tree;
  /**
   * @var string|null
   */
  private ?string $hash = null;

  public function __construct($h, $w, $board = null) {
    $this->h = $h;
    $this->w = $w;
    $this->board = $board;

    //if no custom board is given, create a rectangular one and set all fields to empty
    if (is_null($this->board)) {
      $this->board = array();
      for ($py = 1; $py <= $h; $py++) {
        for ($px = 1; $px <= $w; $px++) {
          $this->board[$px][$py] = null;
        }
      }
    }
  }

  /**
   * @param Tile | Tile[] $tiles
   * @return void
   */
  public function addTiles(Tile|array $tiles): void {
    if ($tiles instanceof Tile) {
      $tiles = [$tiles];
    }
    foreach ($tiles as $tile) {
      $this->board[$tile->x][$tile->y] = $tile;
    }
  }

  /**
   * @return false|Board
   */
  public function solve(): Board|bool {
    $this->prepare();
    $step = $this->getStep();
    $this->tree = new Tree($this);
    while (count($this->tree->steps[$step])) {
      foreach ($this->tree->steps[$step] as $move) {
        /** @var Board $move */
        $moves = $move->getMovesEmpty();
        //check if new constellations are already known by hash...
        foreach ($moves as $m) {
          $valid = $this->tree->addMove($move, $m);
          if ($valid) {
            if ($m->checkGoal()) {
              //found solution!!!
              return $m;
            }
          }
        }

      }
      $step = $m->getStep();
    }
    return false;
  }

  public function isEmpty($x, $y): bool {
    return is_null($this->board[$x][$y]);
  }

  private function prepare(): void {
    for ($py = 1; $py <= $this->h; $py++) {
      for ($px = 1; $px <= $this->w; $px++) {
        if (is_null($this->board[$px][$py])) {
          $e = new Tile('-', $px, $py);
          $e->setEmpty();
          $this->board[$px][$py] = $e;
        }
      }
    }
  }

  /**
   * get all surrounding tiles of all empty places and check if the moves are valid
   * @return Board[]
   */
  private function getMovesEmpty() {
    $moves = array();
    /** @var Tile $e */
    for ($py = 1; $py <= $this->h; $py++) {
      for ($px = 1; $px <= $this->w; $px++) {
        if ($this->board[$px][$py]->isEmpty()) {
          $e = $this->board[$px][$py];
          /** @var Tile $e */
          $tiles = $this->getSurroundingTiles($e);
          foreach ($tiles as $t) {
            $newBoard = $this->checkDependendMove($t, $e);
            if ($newBoard instanceof Board) {
              //Bewegung möglich, als neue Ausgangsposition speichern!
              $moves[] = $newBoard;
            }
          }
        }
      }
    }

    return $moves;
  }

  /**
   * @param Tile $e
   * @return Tile[]
   */
  private function getSurroundingTiles(Tile $e): array {
    $tiles = array();
    $t = array();
    $t[] = @$this->board[$e->x - 1][$e->y];
    $t[] = @$this->board[$e->x + 1][$e->y];
    $t[] = @$this->board[$e->x][$e->y - 1];
    $t[] = @$this->board[$e->x][$e->y + 1];
    foreach ($t as $tile) {
      if (($tile instanceof Tile) && !$tile->isEmpty()) {
        $tiles[] = $tile;
      }
    }
    return $tiles;
  }

  public function clone(): Board|static {
    $clone = clone $this;
    //update tiles
    for ($py = 1; $py <= $this->h; $py++) {
      for ($px = 1; $px <= $this->w; $px++) {
        $clone->board[$px][$py] = clone $clone->board[$px][$py];

      }
    }
    //update synced tiles
    for ($py = 1; $py <= $this->h; $py++) {
      for ($px = 1; $px <= $this->w; $px++) {
        foreach ($clone->board[$px][$py]->syncedTiles as $idx => $s) {
          $clone->board[$px][$py]->syncedTiles[$idx] = $clone->board[$s->x][$s->y];
        }
      }
    }
    $clone->increaseSteps();
    return $clone;
  }

  private function checkDependendMove(Tile $t, Tile $e): Board|bool|static {
    $clone = $this->clone();
    $t = $clone->board[$t->x][$t->y];
    $e = $clone->board[$e->x][$e->y];

    $xrel = $e->x - $t->x;
    $yrel = $e->y - $t->y;
    $clone->move($t, $xrel, $yrel);
    foreach ($t->syncedTiles as $s) {
      if (!$clone->move($s, $xrel, $yrel)) {
        unset($clone);
        return false;
      }
    }
    return $clone;
  }

  /**
   * @param Tile $t
   * @param $xrel
   * @param $yrel
   * @return bool
   */
  private function move(Tile $t, $xrel, $yrel): bool {
    $this->hash = null;
    $bx = $t->x + $xrel;
    $by = $t->y + $yrel;
    $ax = $t->x;
    $ay = $t->y;

    if (!$this->board[$bx][$by]->isEmpty()) {
      return false;
    }
    $b = $this->board[$bx][$by];
    $this->board[$bx][$by] = $t;
    $t->x = $bx;
    $t->y = $by;

    $this->board[$ax][$ay] = $b;
    $b->x = $ax;
    $b->y = $ay;
    return true;
  }

  private function increaseSteps(): void {
    $this->step++;
  }

  public function getHash(): string {
    if (!is_null($this->hash)) return $this->hash;
    $h = '';
    for ($py = 1; $py <= $this->h; $py++) {
      $h .= '|';
      for ($px = 1; $px <= $this->w; $px++) {
        $h .= str_pad($this->board[$px][$py]->name, 4, " ", STR_PAD_BOTH) . "|";
      }
      $h .= "\n";
    }
    $this->hash = $h;
    return $h;
  }

  public function getStep(): int {
    return $this->step;
  }

  private function checkGoal(): bool {
    $result = true;
    for ($py = 1; $py <= $this->h; $py++) {
      for ($px = 1; $px <= $this->w; $px++) {
        if (!$this->board[$px][$py]->checkGoal())
          $result = false;
      }
    }
    return $result;
  }

  public function showSolution(Board|bool $res) {
    $stepsAnz = $res->step;
    echo "------------------ Solution with $stepsAnz Steps ------------------\n";
    $parentHash = $res->hash;
    $steps = array();
    do {
      $steps[] = $this->tree->hashes[$parentHash]['board'];
      $parentHash = @$this->tree->hashes[$parentHash]['parent'];

    } while (!is_null($parentHash));
    $steps = array_reverse($steps);
    $oldStep = array_shift($steps);
    while(!is_null($newStep = array_shift($steps))){
      $oh = $oldStep->getHash();
      $ohx = str_replace(array("\n","|",),array('!',"$"),$oh);
      $nh = $newStep->getHash();


      echo $this->diffstr($ohx,$nh)."\n\n";
      $oldStep = $newStep;
    }

    echo "------------------ Solution with $stepsAnz Steps ------------------\n";

  }

  function diffstr($a, $b){
    $o ="";
    $a = str_split($a);
    $b = str_split($b);
    foreach($b as $i=>$c){
      if ($a[$i] != $c){
        $o.=$c;
      }else{
        $o.=" ";
      }
    }
    return $o;
  }
}