<?php

namespace AdaiasMagdiel\Lura;

use AdaiasMagdiel\Lura\Token\TokenType;

class Builtins
{
	private Error $error;
	private array $functions;

	public function __construct(Error $error)
	{
		$this->error = $error;
		$this->functions = [
			// Functions
			"show" => [
				"fn" => [$this, 'show'],
				"min_args" => 1,
				"max_args" => 1,
				"types" => null
			],
			"echo" => [
				"fn" => [$this, 'echo'],
				"min_args" => 1,
				"max_args" => 1,
				"types" => null
			],
			"concat" => [
				"fn" => [$this, 'concat'],
				"min_args" => 2,
				"max_args" => null,
				"types" => [TokenType::STRING]
			],

			// Operators
			"+" => [
				"fn" => [$this, 'plus'],
				"min_args" => 1,
				"max_args" => null,
				"types" => [TokenType::INT, TokenType::FLOAT]
			]
		];
	}

	// Validators

	/**
	 * @param Token[] $arguments
	 */
	private function validateArity(Token $token, array $arguments): bool
	{
		$fn = $this->functions[$token->value];
		$argumentsLen = count($arguments);

		$maxArgs = is_null($fn["max_args"])
			? true
			: $argumentsLen > $fn["max_args"];

		if ($argumentsLen < $fn["min_args"] && $maxArgs) {
			$min = $fn["min_args"] !== $fn["max_args"]
				? "at least {$fn["min_args"]}"
				: $fn["min_args"];

			$this->error->RuntimeError(
				$fn->loc,
				"$token->value requires {$min} arguments but received $argumentsLen."
			);
			return false;
		}

		return true;
	}

	/**
	 * @param TokenType[] $types
	 */
	private function getTypesMessage(array $types): string
	{
		$typesStr = array_map(function ($type) {
			return $type->name;
		}, $types);

		return implode(" | ", $typesStr);
	}

	/**
	 * @param Token[] $arguments
	 */
	private function validateType(Token $token, array $arguments): bool
	{
		$fn = $this->functions[$token->value];

		if (is_null($fn["types"])) return true;

		foreach ($arguments as $arg) {
			if (!in_array($arg->type, $fn["types"])) {
				$types = $this->getTypesMessage($fn["types"]);

				$this->error->TypeError(
					$arg->loc,
					"Expected $types, got " . $arg->type->name,
					strlen((string) $arg?->value ?? '')
				);

				return false;
			}
		}

		return true;
	}

	public function hasFunc(Token $token): bool
	{
		return isset($this->functions[$token->value]);
	}

	private function TokenNil(Token $token): Token
	{
		return new Token(TokenType::NIL, null, $token->loc);
	}

	// FUNCTIONS

	/**
	 * @param Token[] $arguments
	 */
	public function show(Token $token, array $arguments): Token
	{
		$res = $this->validateArity($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		$res = $this->validateType($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		$value = array_shift($arguments);

		echo $value->value . PHP_EOL;
		return $this->TokenNil($token);
	}

	/**
	 * @param Token[] $arguments
	 */
	public function echo(Token $token, array $arguments): Token
	{
		$res = $this->validateArity($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		$res = $this->validateType($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		$value = array_shift($arguments);

		echo $value->value;
		return $this->TokenNil($token);
	}

	/**
	 * @param Token[] $arguments
	 */
	public function concat(Token $token, array $arguments): Token
	{
		$res = $this->validateArity($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		$res = $this->validateType($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		return new Token(
			TokenType::STRING,
			implode("", array_map(fn($token) => $token->value, $arguments)),
			$token->loc
		);
	}


	// OPERATORS

	/**
	 * @param Token[] $arguments
	 */
	public function plus(Token $token, array $arguments): Token
	{
		$res = $this->validateArity($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		$res = $this->validateType($token, $arguments);
		if (!$res) return $this->TokenNil($token);

		$type = TokenType::INT;
		$sum = 0;

		foreach ($arguments as $arg) {
			if ($arg->type === TokenType::FLOAT) {
				$type = TokenType::FLOAT;
			}

			$sum += $arg->value;
		}

		return new Token($type, $sum, $token->loc);
	}

	/**
	 * @param Token[] $arguments
	 */
	public function call(Token $token, array $arguments): Token
	{
		$fn = $this->functions[$token->value];

		return $fn["fn"]($token, $arguments);
	}
}
