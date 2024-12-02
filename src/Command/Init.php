<?php

namespace Jelle_S\AOC\Command;

use Jelle_S\AOC\Client\AOCClient;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class Init extends Command {

  protected AOCClient $client;
  protected Filesystem $fileSystem;

  public function __construct() {
    parent::__construct();
    $this->client = new AOCClient();
    $this->fileSystem = new Filesystem();
  }

  protected function configure() {
    parent::configure();
    $this
      ->setName('init')
      ->setDescription('Initialise all scaffolding for an aoc puzzle solution.')
      ->addArgument('part', InputArgument::OPTIONAL, 'The part of the puzzle, defaults to part 1', 1)
      ->addArgument('day', InputArgument::OPTIONAL, 'The day of the puzzle, defaults to the current day')
      ->addArgument('year', InputArgument::OPTIONAL, 'The year of the puzzle, defaults to the current year');
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
    $part = (int) $input->getArgument('part');
    return $this->scaffold($year, $day, $part, $io);
  }

  protected function scaffold(int $year, int $day, int $part, SymfonyStyle $io) {
    $formattedDay = sprintf('%02d', $day);
    $dir = "src/AOC$year/Day$formattedDay";
    return (int) ($this->ensureDirectory($dir, $io)
    && $this->ensurePuzzleClass($dir, $year, $formattedDay, $part, $io)
    && $this->ensureSampleData($dir, $year, $day, $io)
    && $this->ensureInputData($dir, $year, $day, $io)
    && $this->ensureChallenge($dir, $year, $day, $part, $io));
  }

  protected function ensureDirectory(string $dir, SymfonyStyle $io) {
    if ($this->fileSystem->exists($dir)) {
      $io->info("$dir already exists.");

      return true;
    }

    $this->fileSystem->mkdir($dir);
    $io->info("Created directory $dir.");

    return true;
  }

  protected function ensurePuzzleClass(string $dir, int $year, string $day, int $part, SymfonyStyle $io): bool {
    $contents = file_get_contents(__DIR__ . "/../../Resources/templates/puzzle/Puzzle$part.tpl");
    if (file_exists("$dir/Puzzle$part.php") && !$io->confirm("File $dir/Puzzle$part.php exists. Do you want to overwrite?")) {
      return false;
    }

    $this->fileSystem->dumpFile(
      "$dir/Puzzle$part.php",
      str_replace(['##YEAR##', '##DAY##'], [$year, $day], $contents)
    );

    $io->info("Created file $dir/Puzzle$part.php.");
    return true;
  }

  protected function ensureSampleData(string $dir, int $year, int $day, SymfonyStyle $io) {
    $this->ensureDirectory("$dir/Resources", $io);
    $this->fileSystem->dumpFile("$dir/Resources/sample.txt", $this->client->getSample($year, $day));
    $io->info("Created file $dir/Resources/sample.txt.");

    return true;
  }

  protected function ensureInputData(string $dir, int $year, int $day, SymfonyStyle $io) {
    $this->ensureDirectory("$dir/Resources", $io);
    $this->fileSystem->dumpFile("$dir/Resources/input.txt", $this->client->getInput($year, $day));
    $io->info("Created file $dir/Resources/input.txt.");

    return true;
  }

  protected function ensureChallenge(string $dir, int $year, int $day, int $part, SymfonyStyle $io) {
    $this->ensureDirectory("$dir/Resources", $io);
    $this->fileSystem->touch("$dir/Resources/challenge.md");
    $converter = new HtmlConverter();
    $this->fileSystem->appendToFile(
      "$dir/Resources/challenge.md",
      $converter->convert($this->client->getChallengeHTML($year, $day, $part)) . PHP_EOL . PHP_EOL
    );

    $io->info("Created file $dir/Resources/challenge.md.");

    return true;
  }
}
