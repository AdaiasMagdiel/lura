<?php

namespace AdaiasMagdiel\Lura\Token;

enum TokenType: string
{
	case PLUS = "+";
	case MINUS = "-";

	case INT = "INT";
	case FLOAT = "FLOAT";
	case STRING = "STRING";

	case IDENTIFIER = "IDENTIFIER";

	case L_PAREN = "(";
	case R_PAREN = ")";

	case EOF = "EOF";
	case NIL = "NIL";
}
