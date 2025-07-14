<?php

namespace AdaiasMagdiel\Lura;

use AdaiasMagdiel\Lura\Token\Loc;
use AdaiasMagdiel\Lura\Token\TokenType;

class Token
{
	public function __construct(
		public TokenType $type,
		public mixed $value,
		public Loc $loc
	) {}
}
