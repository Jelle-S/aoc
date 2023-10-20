<?php

namespace Jelle_S\AOC\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class InitProject extends Command {

  protected Filesystem $fileSystem;

  public function __construct() {
    parent::__construct();
    $this->fileSystem = new Filesystem();
  }

  protected function configure() {
    parent::configure();
    $this
      ->setName('init-project')
      ->setDescription('Initialise all scaffolding for an aoc solution project.')
      ->addArgument('dir', InputArgument::REQUIRED, 'The directory to create the project in')
      ->addArgument('year', InputArgument::OPTIONAL, 'The year of the puzzle, defaults to the current year');
  }

  protected function validateInput(InputInterface $input) {
    $validator = Validation::createValidator();
    $year = $input->getArgument('year');
    $yearConstraints = [
      new Type('numeric'),
      new Range(min: 2015, max: (int) date('Y')),
    ];
    $violations = $validator->validate($year, $yearConstraints);

    return $violations;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $input->setArgument('year', $input->getArgument('year') ?? (int)date('Y'));

    $violations = $this->validateInput($input);
    if ($violations->count()) {
      foreach ($violations as $violation) {
        $io->error($violation->getMessage());
      }

      return Command::FAILURE;
    }

    $year = (int) $input->getArgument('year');
    $dir = $input->getArgument('dir');
    $this->scaffold($year, $dir);

    return 0;
  }

  protected function scaffold(int $year, string $dir) {
    $this->fileSystem->mirror(__DIR__ . '/../../Resources/templates/project', $dir);
    $this->fileSystem->dumpFile("$dir/composer.json", str_replace('##YEAR##', $year, file_get_contents("$dir/composer.json")));
  }
}
