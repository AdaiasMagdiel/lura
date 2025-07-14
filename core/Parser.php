<?php

namespace AdaiasMagdiel\Lura;

use AdaiasMagdiel\Lura\Token\Loc;
use AdaiasMagdiel\Lura\Token\TokenType;
use RuntimeException;

class Parser
{
    /**
     * @var Token[] $tokens
     */
    private array $tokens;
    private int $position = 0;
    private Builtins $builtins;
    private Error $error;

    /**
     * @param Token[] $tokens
     */
    public function __construct(string $program, array $tokens, bool $stopOnError = true)
    {
        $this->tokens = $tokens;
        $this->error = new Error($program, $stopOnError);
        $this->builtins = new Builtins($this->error);
    }

    private function TokenNil(Loc $loc): Token
    {
        return new Token(TokenType::NIL, null, $loc);
    }

    public function evaluate(): array
    {
        $results = [];
        while ($this->tokens[$this->position]->type !== TokenType::EOF) {
            $results[] = $this->parseExpression();
        }
        return $results;
    }

    private function parseExpression(): Token
    {
        $token = $this->tokens[$this->position];

        if ($token->type === TokenType::EOF) {
            return $this->TokenNil(new Loc(-1, -1));
        }

        // Se for átomo (números, strings, símbolos)
        if ($token->type !== TokenType::L_PAREN) {
            $this->position++;

            if ($token->type === TokenType::IDENTIFIER) {
                if (!$this->builtins->hasFunc($token)) {
                    $this->error->NameError(
                        $token->loc,
                        "Name '$token->value' is not defined",
                        strlen($token->value)
                    );

                    return $this->TokenNil($token->loc);
                }
            }

            return $token;
        }

        // Se for lista/expressão entre parênteses
        $this->position++; // Consome '('
        $elements = [];

        while ($this->tokens[$this->position]->type !== TokenType::R_PAREN) {
            $elements[] = $this->parseExpression();
        }

        $this->position++; // Consome ')'
        return $this->evalList($elements);
    }

    /**
     * @param Token[] $elements
     */
    private function evalList(array $elements): Token
    {
        if (empty($elements)) {
            return $this->TokenNil(new Loc(-1, -1));
        }

        $fn = array_shift($elements);
        return $this->builtins->call($fn, $elements);
    }
}
