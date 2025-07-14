<?php

# tests/Unit/ParserTest.php

use AdaiasMagdiel\Lura\Lexer;
use AdaiasMagdiel\Lura\Parser;
use AdaiasMagdiel\Lura\Token\TokenType;

describe('Parser', function () {
    describe('Show Function', function () {
        it('should output integers', function () {
            $program = '(show 123)';
            ob_start();
            $lexer = new Lexer($program, false);
            $tokens = $lexer->tokenize();
            $parser = new Parser($program, $tokens, false);
            $results = $parser->evaluate();
            $output = ob_get_clean();

            expect($results)->toHaveCount(1)
                ->and($results[0]->type)->toBe(TokenType::NIL)
                ->and($output)->toContain('123');
        });

        it('should output floats', function () {
            $program = '(show 3.14)';
            ob_start();
            $lexer = new Lexer($program, false);
            $tokens = $lexer->tokenize();
            $parser = new Parser($program, $tokens, false);
            $results = $parser->evaluate();
            $output = ob_get_clean();

            expect($results)->toHaveCount(1)
                ->and($results[0]->type)->toBe(TokenType::NIL)
                ->and($output)->toContain('3.14');
        });

        it('should output strings', function () {
            $program = '(show "hello")';
            ob_start();
            $lexer = new Lexer($program, false);
            $tokens = $lexer->tokenize();
            $parser = new Parser($program, $tokens, false);
            $results = $parser->evaluate();
            $output = ob_get_clean();

            expect($results)->toHaveCount(1)
                ->and($results[0]->type)->toBe(TokenType::NIL)
                ->and($output)->toContain('hello');
        });
    });

    describe('Expression Parsing', function () {
        it('should handle nested expressions', function () {
            $program = '(show (+ 1 2))';
            ob_start();
            $lexer = new Lexer($program, false);
            $tokens = $lexer->tokenize();
            $parser = new Parser($program, $tokens, false);
            $results = $parser->evaluate();
            $output = ob_get_clean();

            expect($results)->toHaveCount(1)
                ->and($results[0]->type)->toBe(TokenType::NIL)
                ->and($output)->toContain('3');
        });

        it('should handle empty expressions', function () {
            $program = '()';
            $lexer = new Lexer($program, false);
            $tokens = $lexer->tokenize();
            $parser = new Parser($program, $tokens, false);
            $results = $parser->evaluate();

            expect($results)->toHaveCount(1)
                ->and($results[0]->type)->toBe(TokenType::NIL);
        });
    });

    describe('Error Handling', function () {
        it('should report error for undefined functions', function () {
            $program = '(unknown 123)';
            ob_start();
            $lexer = new Lexer($program, false);
            $tokens = $lexer->tokenize();
            $parser = new Parser($program, $tokens, false);
            $results = $parser->evaluate();
            $output = ob_get_clean();

            expect($results)->toHaveCount(1)
                ->and($results[0]->type)->toBe(TokenType::NIL)
                ->and($output)->toContain("Name 'unknown' is not defined");
        });
    });
});
