<?php

namespace Jelle_S\AOC\Command;

use Jelle_S\AOC\Contracts\PuzzleInterface;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class Run extends Command {

  protected function configure() {
    parent::configure();
    $this
      ->setName('run')
      ->setDescription('Run a puzzle solution.')
      ->addArgument('part', InputArgument::OPTIONAL, 'The part of the puzzle, defaults to part 1', 1)
      ->addArgument('day', InputArgument::OPTIONAL, 'The day of the puzzle, defaults to the current day')
      ->addArgument('year', InputArgument::OPTIONAL, 'The year of the puzzle, defaults to the current year')
      ->addOption('sample', 's', InputOption::VALUE_OPTIONAL, 'Run the sample, not the input', false);
  }

  protected function validateInput(InputInterface $input) {
    $validator = Validation::createValidator();
    // Todo: future dates are not valid either.
    // Todo: clean validation error messages.

    $part = $input->getArgument('part');
    $partConstraints = [
      new Type('numeric'),
      new Range(min: 1, max: 2),
    ];
    $violations = $validator->validate($part, $partConstraints);

    $day = $input->getArgument('day');
    $dayConstraints = [
      new Type('numeric'),
      new Range(min: 1, max: 25),
    ];
    $violations->addAll($validator->validate($day, $dayConstraints));

    $year = $input->getArgument('year');
    $yearConstraints = [
      new Type('numeric'),
      new Range(min: 2015, max: (int) date('Y')),
    ];
    $violations->addAll($validator->validate($year, $yearConstraints));

    return $violations;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $input->setArgument('day', $input->getArgument('day') ?? (int)date('j'));
    $input->setArgument('year', $input->getArgument('year') ?? (int)date('Y'));

    $violations = $this->validateInput($input);
    if ($violations->count()) {
      foreach ($violations as $violation) {
        $io->error($violation->getMessage());
      }

      return Command::FAILURE;
    }

    $year = (int) $input->getArgument('year');
    $day = (int) $input->getArgument('day');
    $formattedDay = sprintf('%02d', $day);
    $part = (int) $input->getArgument('part');

    $puzzleClass = "Jelle_S\\AOC\\AOC$year\\Day$formattedDay\\Puzzle$part";

    if (!class_exists($puzzleClass)) {
      $io->error("Class $puzzleClass does not exist.");
      return Command::FAILURE;
    }

    if (!is_a($puzzleClass, PuzzleInterface::class, true)) {
      $io->error("Class $puzzleClass does not implement " . PuzzleInterface::class);
      return Command::FAILURE;
    }

    $file = $input->getOption('sample') !== false ? "src/AOC$year/Day$formattedDay/Resources/sample.txt" : "src/AOC$year/Day$formattedDay/Resources/input.txt";
    $io->info((new $puzzleClass($file))->solve());
    return 0;
  }

  protected function scaffold(int $year, int $day, int $part) {
    $formattedDay = sprintf('%02d', $day);
    $dir = "src/AOC$year/Day$formattedDay";
    $this->ensureDirectory($dir);
    $this->ensurePuzzleClass($dir, $year, $formattedDay, $part);
    $this->ensureSampleData($dir, $year, $day);
    $this->ensureInputData($dir, $year, $day);
    $this->ensureChallenge($dir, $year, $day, $part);
  }

  protected function ensureDirectory(string $dir) {
    $this->fileSystem->mkdir($dir);
  }

  protected function ensurePuzzleClass(string $dir, int $year, string $day, int $part) {
    $contents = file_get_contents(__DIR__ . "/../../Resources/templates/puzzle/Puzzle$part.tpl");
    $this->fileSystem->dumpFile(
      "$dir/Puzzle$part.php",
      str_replace(['##YEAR##', '##DAY##'], [$year, $day], $contents)
    );
  }

  protected function ensureSampleData(string $dir, int $year, int $day) {
    $this->ensureDirectory("$dir/Resources");
    $this->fileSystem->dumpFile("$dir/Resources/sample.txt", $this->client->getSample($year, $day));
  }

  protected function ensureInputData(string $dir, int $year, int $day) {
    $this->ensureDirectory("$dir/Resources");
    $this->fileSystem->dumpFile("$dir/Resources/input.txt", $this->client->getInput($year, $day));
  }

  protected function ensureChallenge(string $dir, int $year, int $day, int $part) {
    $this->ensureDirectory("$dir/Resources");
    $this->fileSystem->touch("$dir/Resources/challenge.md");
    $converter = new HtmlConverter();
    $this->fileSystem->appendToFile(
      "$dir/Resources/challenge.md",
      $converter->convert($this->client->getChallengeHTML($year, $day, $part)) . PHP_EOL . PHP_EOL
    );
  }
}
