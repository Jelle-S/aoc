# AOC

This repository contains aoc commands to have the basics for an aoc solution
project up and running with a single command.

## Installation

```
composer require jelle-s/aoc
```

## Configuration

Following environment variables can/should be configured:

- `AOC_COOKIE`: Your advent of code session cookie.

## Commands

### Init

```
vendor/bin/aoc init 1 1 2022
```

```
Description:
  Initialise all scaffolding for an aoc puzzle solution.

Usage:
  init [<part> [<day> [<year>]]]

Arguments:
  part                  The part of the puzzle, defaults to part 1 [default: 1]
  day                   The day of the puzzle, defaults to the current day
  year                  The year of the puzzle, defaults to the current year

Options:
  -h, --help            Display help for the given command. When no command is given display help for the run command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### InitProject

```
vendor/bin/aoc init-project ~/projects/aoc2022 2022
```

```
Description:
  Initialise all scaffolding for an aoc solution project.

Usage:
  init-project <dir> [<year>]

Arguments:
  dir                   The directory to create the project in
  year                  The year of the puzzle, defaults to the current year

Options:
  -h, --help            Display help for the given command. When no command is given display help for the run command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```

### Run [default command]

```
vendor/bin/aoc run 1 1 2022
```

```
Description:
  Run a puzzle solution.

Usage:
  run [<part> [<day> [<year>]]]

Arguments:
  part                  The part of the puzzle, defaults to part 1 [default: 1]
  day                   The day of the puzzle, defaults to the current day
  year                  The year of the puzzle, defaults to the current year

Options:
  -h, --help            Display help for the given command. When no command is given display help for the run command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```
