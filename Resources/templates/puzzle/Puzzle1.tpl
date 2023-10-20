<?php

namespace Jelle_S\AOC\AOC##YEAR##\Day##DAY##;

use Jelle_S\AOC\Contracts\PuzzleInterface;

class Puzzle1 implements PuzzleInterface {

  public function __construct(protected string $input) {
  }

  public function solve() {
    $result = 0;

    $h = fopen($this->input, 'r');

    while (($line = fgets($h)) !== false) {
      $line = trim($line);

    }
    fclose($h);

    return $result;
  }
}