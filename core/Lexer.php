<?php

namespace AdaiasMagdiel\Lura;

use AdaiasMagdiel\Lura\Error;
use AdaiasMagdiel\Lura\Token\Loc;
use AdaiasMagdiel\Lura\Token\TokenType;

class Lexer
{
	private string $program;
	private int $col = 1;
	private int $line = 1;
	private int $pos = 0;
	private Error $error;

	public function __construct(string $program, bool $stopOnError = true)
	{
		$this->program = $program;
		$this->error = new Error($program, $stopOnError);
	}

	private function EOF(): bool
	{
		return $this->pos >= strlen($this->program);
	}

	private function peek(int $length = 1): string
	{
		return substr($this->program, $this->pos, $length);
	}

	private function consume(int $length = 1): string
	{
		$value = $this->peek($length);

		$this->pos += $length;
		$this->col += $length;

		return $value;
	}

	private function putback(int $length = 1): void
	{
		$this->pos -= $length;
		$this->col -= $length;
	}

	private function getLoc(): Loc
	{
		return new Loc($this->line, $this->col);
	}

	private function makeUnaryToken(array &$tokens, TokenType $type): void
	{
		$loc = new Loc($this->line, $this->col);
		$value = $this->consume();
		$token = new Token($type, $value, $loc);
		$tokens[] = $token;
	}

	private function isAlpha(string $value): bool
	{
		return 'a' <= strtolower($value[0]) && strtolower($value[0]) <= 'z';
	}

	private function isNum(string $value): bool
	{
		return '0' <= $value[0] && $value[0] <= '9';
	}

	private function isAlphaNum(string $value)
	{
		return $this->isAlpha($value) or $this->isNum($value);
	}

	private function isIdentifierChar(string $value)
	{
		return $this->isAlphaNum($value) or $value === '_';
	}

	private function isWhitespace(string $char): bool
	{
		return match ($char) {
			' ',      // Espaço comum
			"\t",     // Tabulação
			"\r",     // Retorno de carro (CR)
			"\v",     // Tab vertical
			"\f",     // Quebra de página
			"\u{00A0}" // Espaço insecável
			=> true,
			default => false
		};
	}

	// Extractors
	private function extractWhile(callable $predicate): string
	{
		$chars = [];

		while (true) {
			if ($this->EOF()) break;

			$ch = $this->peek();
			if ($predicate($ch)) {
				$chars[] = $this->consume();
			} else {
				break;
			}
		}

		return implode('', $chars);
	}

	private function extractIdentifier(): string
	{
		return $this->extractWhile(fn($ch) => $this->isIdentifierChar($ch));
	}

	private function extractNumber(): Token
	{
		$loc = $this->getLoc();
		$chars = [];

		$type = TokenType::INT;

		while (true) {
			if ($this->EOF()) break;

			$ch = $this->peek();

			// Is number - 0-9
			if ($this->isNum($ch)) {
				$chars[] = $this->consume();
			}

			// Decimal point
			else if ($ch === '.') {
				$dot = $this->consume();

				if (!$this->isNum($this->peek())) {
					$this->error->SyntaxError(
						$this->getLoc(),
						"Invalid decimal number format - expected digit after decimal point"
					);
					break;
				}

				// First .
				if (
					$type === TokenType::INT
				) {
					$type = TokenType::FLOAT;
					$chars[] = $dot;
				} else {
					$this->error->SyntaxError(
						$this->getLoc(),
						"Invalid numeric literal - multiple decimal points detected"
					);

					break;
				}
			}

			// Underscore
			else if ($ch === '_') {
				$this->consume();

				if (!$this->isNum($this->peek())) {
					$this->putback();
					break;
				}
			} else {
				break;
			}
		}

		$value = implode('', $chars);
		if ($type === TokenType::INT) {
			return new Token($type, intval($value), $loc);
		} else {
			return new Token($type, floatval($value), $loc);
		}
	}

	private function extractComment(): void
	{
		$this->extractWhile(fn($ch) => $ch !== "\n");
	}

	private function extractMultilineComment(): void
	{
		$loc = $this->getLoc(); // Guarda a posição inicial para mensagens de erro

		while (true) {
			if ($this->EOF()) {
				$this->error->SyntaxError(
					$loc,
					"Unterminated block comment - expected '-#' to close"
				);
				break;
			}

			if ($this->peek(2) === '-#') {
				$this->consume(2); // Remove o fechamento -#
				break;
			}

			// Conta novas linhas dentro do comentário
			if ($this->peek() === "\n") {
				$this->line++;
				$this->col = 1;
			} else {
				$this->col++;
			}

			$this->consume();
		}
	}

	private function extractString(): string
	{
		$loc = $this->getLoc();
		$chars = [];
		$stringType = $this->consume(); // remove ' or "

		while (true) {
			if ($this->EOF()) {
				$this->error->SyntaxError(
					$loc,
					"Unterminated string literal - missing closing quote"
				);
				break;
			}

			$ch = $this->peek();

			if ($ch === "\n") {
				$this->error->SyntaxError(
					$loc,
					"Unterminated string literal",
					$this->col - $loc->col - 2
				);
				break;
			}

			$ESCAPE_SEQUENCES = [
				"\\'" => "'",
				'\\"' => '"',
				'\\n' => "\n",
				'\\t' => "\t",
				'\\r' => "\r",
				'\\\\' => "\\"
			];

			if ($ch === '\\') {
				$sequence = $this->peek(2);

				if (array_key_exists($sequence, $ESCAPE_SEQUENCES)) {
					$this->consume(2);
					$chars[] = $ESCAPE_SEQUENCES[$sequence];
				} else {
					$chars[] = $this->consume(2);
				}
			} else if ($ch === $stringType) {
				$this->consume(); // remove ' or "
				break;
			} else {
				$chars[] = $this->consume();
			}
		}

		return implode('', $chars);
	}

	/**
	 * @return Token[];
	 */
	public function tokenize(): array
	{
		$tokens = [];
		$lists = [];

		while (true) {
			if ($this->EOF()) break;

			$ch = $this->peek();

			// Comments
			if ($ch === '#') {
				if ($this->peek(2) === '#-') {
					$this->extractMultilineComment();
				} else {
					$this->extractComment();
				}
			}

			// New Line
			else if ($ch === "\n") {
				$this->consume();
				$this->line++;
				$this->col = 1;
			}

			// Strings - ' and "
			else if ($ch === "'" or $ch === '"') {
				$loc = $this->getLoc();
				$value = $this->extractString();

				$tokens[] = new Token(TokenType::STRING, $value, $loc);
			}

			// PLUS
			else if ($ch === "+") {
				$this->makeUnaryToken($tokens, TokenType::PLUS);
			}

			// PARENS
			else if ($ch === "(") {
				$lists[] = $this->getLoc();
				$this->makeUnaryToken($tokens, TokenType::L_PAREN);
			} else if ($ch === ")") {
				$this->makeUnaryToken($tokens, TokenType::R_PAREN);
				array_pop($lists);
			}

			//NUMBERS
			else if ($this->isNum($ch)) {
				$token = $this->extractNumber();
				$tokens[] = $token;
			}

			// IDENTIFIER
			else if (
				$ch === "_" ||
				$this->isAlpha($ch)
			) {
				$loc = $this->getLoc();
				$value = $this->extractIdentifier();
				$tokens[] = new Token(TokenType::IDENTIFIER, $value, $loc);
			} else if (
				$ch === " "  ||
				$ch === "\t" ||
				$ch === "\r" // Col shoud back -1???
			) {
				$this->consume();
			}

			// Whitespaces
			else if ($this->isWhitespace($ch)) {
				$this->consume();
			}

			// Default case
			else {
				$this->error->SyntaxError(
					$this->getLoc(),
					"Unexpected character '{$ch}' - invalid syntax"
				);
				return [new Token(TokenType::EOF, null, $this->getLoc())];
			}
		}

		$tokens[] = new Token(TokenType::EOF, null, $this->getLoc());

		if ($tokens[0]->type !== TokenType::L_PAREN) {
			$this->error->SyntaxError(
				$tokens[0]->loc,
				"Top-level expressions must be wrapped in parentheses.",
				strlen($tokens[0]?->value ?? '')
			);
			return [new Token(TokenType::EOF, null, $this->getLoc())];
		}

		if (!empty($lists)) {
			$this->error->SyntaxError(
				$lists[0],
				"Unclosed expression, maybe you forgot to close parentheses?"
			);
			return [new Token(TokenType::EOF, null, $this->getLoc())];
		}

		return $tokens;
	}
}
