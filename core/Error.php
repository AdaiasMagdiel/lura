<?php

namespace AdaiasMagdiel\Lura;

use AdaiasMagdiel\Lura\Error\ExitCode;
use AdaiasMagdiel\Lura\Token\Loc;
use AdaiasMagdiel\Lura\Utils\Color;

class Error
{
	/**
	 * @var string[] $lines;
	 */
	private array $lines;

	function __construct(private string $program, private bool $stopOnError)
	{
		$this->lines = explode("\n", $program);
	}

	private function formatErrorInfo(int $spacesTotal, int $col, int $tail): string
	{
		$col = max(1, $col);
		$tail = max(0, $tail);
		$spacesTotal = max(0, $spacesTotal);

		$maxLength = 1000;
		$spaces = str_repeat(" ", min($spacesTotal, $maxLength));
		$indicator = str_repeat(" ", min($col - 1, $maxLength)) . "^";
		$tail = $tail > 0 ? str_repeat("^", min($tail - 1, $maxLength)) : '';

		return Color::RED($spaces . $indicator . $tail);
	}

	private function GenericError(string $error, Loc $loc, string $msg, int $tail = 0): void
	{
		/*
		 *   <error>: <msg>
		 *
		 *   <line>:<col> | <lines[line]>
		 *   [    space    ]^^^^^^^^^^^^
		 */

		$line = $this->lines[$loc->line - 1];
		$offset = 0;
		$lineColInfo = Color::RED("{$loc->line}:{$loc->col} | ", $offset);
		$lineColInfoLen = strlen($lineColInfo) - $offset;

		echo Color::RED("$error: ") . $msg . PHP_EOL;
		echo PHP_EOL;
		echo $lineColInfo . $line . PHP_EOL;
		echo $this->formatErrorInfo($lineColInfoLen, $loc->col, $tail, $line);
		echo PHP_EOL;
	}

	public function SyntaxError(Loc $loc, string $msg, int $tail = 0): void
	{
		$this->GenericError("SyntaxError", $loc, $msg, $tail);

		if ($this->stopOnError) {
			exit(ExitCode::SYNTAX_ERROR);
		}
	}

	public function RuntimeError(Loc $loc, string $msg, int $tail = 0): void
	{
		$this->GenericError("RuntimeError", $loc, $msg, $tail);

		if ($this->stopOnError) {
			exit(ExitCode::RUNTIME_ERROR);
		}
	}

	public function TypeError(Loc $loc, string $msg, int $tail = 0): void
	{
		$this->GenericError("TypeError", $loc, $msg, $tail);

		if ($this->stopOnError) {
			exit(ExitCode::RUNTIME_ERROR);
		}
	}

	public function NameError(Loc $loc, string $msg, int $tail = 0): void
	{
		$this->GenericError("NameError", $loc, $msg, $tail);

		if ($this->stopOnError) {
			exit(ExitCode::TYPE_ERROR);
		}
	}
}
