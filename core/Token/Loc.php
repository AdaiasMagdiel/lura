<?php

namespace AdaiasMagdiel\Lura\Token;

class Loc
{
	public function __construct(public int $line, public int $col) {}

	public function __toString()
	{
		return "$this->line:$this->col";
	}
}
