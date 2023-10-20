<?php

namespace Jelle_S\AOC\Contracts;

interface PuzzleInterface {

  public function __construct(string $input);
  public function solve();
}
