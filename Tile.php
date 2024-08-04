<?php

namespace schieberaetsel;

/**
 *
 */
class Tile {
  /**
   * @var
   */
  var $x;
  /**
   * @var
   */
  var $y;
  /**
   * @var
   */
  var $name;
  /**
   * @var array
   */
  var $syncedTiles = array();
  private int $gx = -1;
  private int $gy = -1;
  /**
   * @var true
   */
  private bool $empty = false;


  /**
   * @param $name
   * @param $x
   * @param $y
   * @param  $syncedTiles Tile | Tile[]
   */
  public function __construct($name, $x, $y, array|Tile $syncedTiles = array()) {
    $this->name = $name;
    $this->x = $x;
    $this->y = $y;
    if ($syncedTiles instanceof Tile) {
      $syncedTiles = [$syncedTiles];
    }
    foreach ($syncedTiles as $tile) {
      //add syncedTile to this Tile
      $this->addSyncedTile($tile);
      //add this Tile to syncedTiles
      $tile->addSyncedTile($this);
    }
  }

  public function addSyncedTile(Tile $tile) {
    $this->syncedTiles[] = $tile;
  }

  public function addGoal(int $x, int $y) {
    $this->gx = $x;
    $this->gy = $y;
  }

  public function checkGoal(){
    if($this->gx != -1 ){
      return ($this->x == $this->gx && $this->gy == $this->y);
    }else {
      return true;
    }
  }

  public function setEmpty() {
    $this->empty = true;
  }

  public function isEmpty(): bool {
    return $this->empty;

  }

}