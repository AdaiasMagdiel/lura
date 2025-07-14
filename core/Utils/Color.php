<?php

namespace AdaiasMagdiel\Lura\Utils;

class Color
{
	private static function COLOR(string $value, string $color, ?int &$offset = NULL): string
	{
		$offset = strlen("$color\x1b[0m");
		return "$color" . $value . "\x1b[0m";
	}

	public static function RED(string $value, ?int &$offset = NULL): string
	{
		return self::COLOR($value, "\x1b[031m", $offset);
	}

	public static function GREEN(string $value, ?int &$offset = NULL): string
	{
		return self::COLOR($value, "\x1b[032m", $offset);
	}
}
