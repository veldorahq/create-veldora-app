<?php

declare(strict_types=1);

/**
 * Veldora Console Polyfill
 *
 * Provides a complete zero-dependency compatibility layer for Symfony Console
 * components so that all Veldora CLI commands can execute without requiring
 * external composer dependencies installed.
 */

namespace Symfony\Component\Console\Input {
    if (!interface_exists(InputInterface::class, false)) {
        interface InputInterface
        {
            public function getArgument(string $name): mixed;
            public function getArguments(): array;
            public function getOption(string $name): mixed;
            public function getOptions(): array;
            public function hasArgument(string $name): bool;
            public function hasOption(string $name): bool;
        }
    }

    if (!class_exists(InputOption::class, false)) {
        class InputOption
        {
            public const VALUE_NONE = 1;
            public const VALUE_REQUIRED = 2;
            public const VALUE_OPTIONAL = 4;
            public const VALUE_IS_ARRAY = 8;
            public const VALUE_NEGATABLE = 16;

            public function __construct(
                protected string $name,
                protected string|array|null $shortcut = null,
                protected ?int $mode = null,
                protected string $description = '',
                protected mixed $default = null
            ) {}
        }
    }

    if (!class_exists(InputArgument::class, false)) {
        class InputArgument
        {
            public const REQUIRED = 1;
            public const OPTIONAL = 2;
            public const IS_ARRAY = 4;

            public function __construct(
                protected string $name,
                protected ?int $mode = null,
                protected string $description = '',
                protected mixed $default = null
            ) {}
        }
    }

    if (!class_exists(ArrayInput::class, false)) {
        class ArrayInput implements InputInterface
        {
            public function __construct(protected array $parameters = []) {}

            public function getArgument(string $name): mixed
            {
                return $this->parameters[$name] ?? $this->parameters['--' . $name] ?? null;
            }

            public function getArguments(): array
            {
                return $this->parameters;
            }

            public function getOption(string $name): mixed
            {
                return $this->parameters['--' . $name] ?? $this->parameters[$name] ?? null;
            }

            public function getOptions(): array
            {
                return $this->parameters;
            }

            public function hasArgument(string $name): bool
            {
                return array_key_exists($name, $this->parameters);
            }

            public function hasOption(string $name): bool
            {
                return array_key_exists('--' . $name, $this->parameters) || array_key_exists($name, $this->parameters);
            }
        }
    }
}

namespace Symfony\Component\Console\Output {
    use Symfony\Component\Console\Formatter\OutputFormatterInterface;

    if (!interface_exists(OutputInterface::class, false)) {
        interface OutputInterface
        {
            public function write(string|iterable $messages, bool $newline = false): void;
            public function writeln(string|iterable $messages): void;
        }
    }

    if (!class_exists(ConsoleOutput::class, false)) {
        class ConsoleOutput implements OutputInterface
        {
            public function writeln(string|iterable $messages): void
            {
                $messages = is_iterable($messages) ? $messages : [$messages];
                foreach ($messages as $message) {
                    echo $this->stripTags((string) $message) . "\n";
                }
            }

            public function write(string|iterable $messages, bool $newline = false): void
            {
                $messages = is_iterable($messages) ? $messages : [$messages];
                foreach ($messages as $message) {
                    echo $this->stripTags((string) $message) . ($newline ? "\n" : '');
                }
            }

            protected function stripTags(string $text): string
            {
                // Simple color tag stripping or passthrough for ANSI terminals
                return preg_replace('/<[^>]*>/', '', $text) ?? $text;
            }
        }
    }

    if (!class_exists(NullOutput::class, false)) {
        class NullOutput implements OutputInterface
        {
            public function write(string|iterable $messages, bool $newline = false): void {}
            public function writeln(string|iterable $messages): void {}
        }
    }
}

namespace Symfony\Component\Console\Command {
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Output\ConsoleOutput;

    if (!class_exists(Command::class, false)) {
        class Command
        {
            public const SUCCESS = 0;
            public const FAILURE = 1;
            public const INVALID = 2;

            protected ?string $name = null;
            protected ?string $description = null;
            protected ?string $help = null;
            protected array $arguments = [];
            protected array $options = [];

            public function __construct(?string $name = null)
            {
                if ($name !== null) {
                    $this->name = $name;
                }
                $this->configure();
            }

            protected function configure(): void {}

            public function setName(string $name): static
            {
                $this->name = $name;
                return $this;
            }

            public function getName(): ?string
            {
                return $this->name;
            }

            public function setDescription(string $description): static
            {
                $this->description = $description;
                return $this;
            }

            public function getDescription(): ?string
            {
                return $this->description;
            }

            public function setHelp(string $help): static
            {
                $this->help = $help;
                return $this;
            }

            public function getHelp(): ?string
            {
                return $this->help;
            }

            public function addOption(string $name, ?string $shortcut = null, ?int $mode = null, string $description = '', mixed $default = null): static
            {
                $this->options[$name] = [
                    'shortcut' => $shortcut,
                    'mode' => $mode,
                    'description' => $description,
                    'default' => $default,
                ];
                return $this;
            }

            public function addArgument(string $name, ?int $mode = null, string $description = '', mixed $default = null): static
            {
                $this->arguments[$name] = [
                    'mode' => $mode,
                    'description' => $description,
                    'default' => $default,
                ];
                return $this;
            }

            public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
            {
                $input ??= new ArrayInput();
                $output ??= new ConsoleOutput();

                return $this->execute($input, $output);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                if (method_exists($this, 'executeDirect')) {
                    $this->executeDirect();
                    return self::SUCCESS;
                }
                return self::SUCCESS;
            }

            protected function getHelper(string $name): mixed
            {
                return new class {
                    public function ask(mixed $input, mixed $output, mixed $question): mixed
                    {
                        return true;
                    }
                };
            }

            public function getHelperSet(): mixed
            {
                return new class {
                    public function get(string $name): mixed
                    {
                        return new class {
                            public function ask(mixed $input, mixed $output, mixed $question): mixed
                            {
                                return true;
                            }
                        };
                    }
                };
            }
        }
    }
}

namespace Symfony\Component\Console\Helper {
    use Symfony\Component\Console\Output\OutputInterface;

    if (!class_exists(Table::class, false)) {
        class Table
        {
            protected array $headers = [];
            protected array $rows = [];
            protected string $style = 'default';

            public function __construct(protected ?OutputInterface $output = null) {}

            public function setHeaders(array $headers): static
            {
                $this->headers = $headers;
                return $this;
            }

            public function setRows(array $rows): static
            {
                $this->rows = $rows;
                return $this;
            }

            public function addRow(array $row): static
            {
                $this->rows[] = $row;
                return $this;
            }

            public function setStyle(string $style): static
            {
                $this->style = $style;
                return $this;
            }

            public function render(): void
            {
                if (!empty($this->headers)) {
                    echo "  " . implode('  |  ', $this->headers) . "\n";
                    echo "  " . str_repeat('─', 60) . "\n";
                }
                foreach ($this->rows as $row) {
                    echo "  " . implode('  |  ', $row) . "\n";
                }
            }
        }
    }

    if (!class_exists(QuestionHelper::class, false)) {
        class QuestionHelper
        {
            public function ask(mixed $input, mixed $output, mixed $question): mixed
            {
                return true;
            }
        }
    }
}

namespace Symfony\Component\Console\Question {
    if (!class_exists(Question::class, false)) {
        class Question
        {
            public function __construct(public string $question, public mixed $default = null) {}
        }
    }

    if (!class_exists(ConfirmationQuestion::class, false)) {
        class ConfirmationQuestion extends Question
        {
            public function __construct(string $question, bool $default = true)
            {
                parent::__construct($question, $default);
            }
        }
    }

    if (!class_exists(ChoiceQuestion::class, false)) {
        class ChoiceQuestion extends Question
        {
            public function __construct(string $question, public array $choices = [], mixed $default = null)
            {
                parent::__construct($question, $default);
            }
        }
    }
}

namespace Symfony\Component\Console {
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Output\ConsoleOutput;

    if (!class_exists(Application::class, false)) {
        class Application
        {
            /** @var array<string, Command> */
            protected array $commands = [];

            public function __construct(public string $name = 'Veldora', public string $version = '0.5.0') {}

            public function add(Command $command): ?Command
            {
                $name = $command->getName();
                if ($name !== null) {
                    $this->commands[$name] = $command;
                }
                return $command;
            }

            public function find(string $name): Command
            {
                if (isset($this->commands[$name])) {
                    return $this->commands[$name];
                }
                throw new \InvalidArgumentException("Command [{$name}] is not defined.");
            }

            public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
            {
                global $argv;
                $commandName = $argv[1] ?? 'help';

                if (isset($this->commands[$commandName])) {
                    return $this->commands[$commandName]->run($input, $output);
                }

                echo "\n  ▲ {$this->name} {$this->version}\n\n";
                echo "  Available commands:\n";
                foreach ($this->commands as $name => $cmd) {
                    $desc = $cmd->getDescription() ?? '';
                    printf("    \033[36m%-25s\033[0m %s\n", $name, $desc);
                }
                echo "\n";
                return 0;
            }
        }
    }
}
