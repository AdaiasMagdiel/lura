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

	private function formatErrorInfo(
		int $spacesTotal,
		int $col,
		int $tail,
	): string {
		$spaces = str_repeat(" ", $spacesTotal);
		$indicator = str_repeat(" ", ($col - 1)) . "^";
		$tail = $tail > 0 ? str_repeat("^", $tail - 1) : '';

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
