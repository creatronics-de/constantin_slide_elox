<?php
namespace  schieberaetsel;
class Tree {
  private $root;
  public $hashes = array();
  /**
   * @var Board[] $steps
   */
  public array $steps = array();

  public function __construct(Board $board) {
    $this->root['board'] =$board;
    $this->hashes[$board->getHash()]['board'] = $board;
    $this->hashes[$board->getHash()]['step'] = $board->getStep();
    $this->steps[$board->getStep()][]=$board;
  }

  public function addMove(Board $base, Board $move): bool {
    if(isset($this->hashes[$move->getHash()])){
      return false;
    }
    $this->hashes[$move->getHash()]['board'] = $move ;
    $this->hashes[$move->getHash()]['parent'] = $base->getHash();
    $this->hashes[$move->getHash()]['step'] = $move->getStep();
    $this->steps[$move->getStep()][]=$move;

    return true;
  }


}