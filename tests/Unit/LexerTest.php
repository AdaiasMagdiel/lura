<?php

# tests/Unit/LexerTest.php

use AdaiasMagdiel\Lura\Lexer;
use AdaiasMagdiel\Lura\Token\TokenType;

describe('Lexer', function () {
    describe('Comments', function () {
        it('should ignore single-line comments', function () {
            $lexer = new Lexer('# This is a single-line comment', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)
                ->toHaveCount(1)
                ->and($tokens[0]->type)
                ->toBe(TokenType::EOF);
        });

        it('should terminate single-line comments at newline', function () {
            $lexer = new Lexer(
                <<<CODE
                # This is a comment
                (show "Hello")
                CODE,
                stopOnError: false
            );
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(5)
                ->and($tokens[0]->type)->toBe(TokenType::L_PAREN)
                ->and($tokens[1]->type)->toBe(TokenType::IDENTIFIER)
                ->and($tokens[2]->type)->toBe(TokenType::STRING)
                ->and($tokens[3]->type)->toBe(TokenType::R_PAREN)
                ->and($tokens[4]->type)->toBe(TokenType::EOF);
        });

        it('should ignore multiline comments', function () {
            $lexer = new Lexer(
                <<<CODE
                #- This is a
                multiline comment -#
                CODE,
                stopOnError: false
            );
            $tokens = $lexer->tokenize();

            expect($tokens)
                ->toHaveCount(1)
                ->and($tokens[0]->type)
                ->toBe(TokenType::EOF);
        });

        it('should ignore code inside multiline comments', function () {
            $lexer = new Lexer(
                <<<CODE
                #- (show "This should be ignored")
                (+ 1 2) -#
                CODE,
                stopOnError: false
            );
            $tokens = $lexer->tokenize();

            expect($tokens)
                ->toHaveCount(1)
                ->and($tokens[0]->type)
                ->toBe(TokenType::EOF);
        });

        it('should report error for unclosed multiline comments', function () {
            ob_start();
            $lexer = new Lexer(
                <<<CODE
                #- Unclosed comment
                (show "This will fail")
                CODE,
                stopOnError: false
            );
            $lexer->tokenize();
            $output = ob_get_clean();

            expect($output)
                ->toContain('Unterminated block comment')
                ->toContain("expected '-#' to close");
        });
    });

    describe('Numbers', function () {
        it('should tokenize integers correctly', function () {
            $lexer = new Lexer('(123)', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[0]->type)->toBe(TokenType::L_PAREN)
                ->and($tokens[1]->type)->toBe(TokenType::INT)
                ->and($tokens[1]->value)->toBe(123)
                ->and($tokens[2]->type)->toBe(TokenType::R_PAREN)
                ->and($tokens[3]->type)->toBe(TokenType::EOF);
        });

        it('should tokenize floats correctly', function () {
            $lexer = new Lexer('(3.14)', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[0]->type)->toBe(TokenType::L_PAREN)
                ->and($tokens[1]->type)->toBe(TokenType::FLOAT)
                ->and($tokens[1]->value)->toBe(3.14)
                ->and($tokens[2]->type)->toBe(TokenType::R_PAREN)
                ->and($tokens[3]->type)->toBe(TokenType::EOF);
        });

        it('should tokenize numbers with underscores', function () {
            $lexer = new Lexer('(1_000_000)', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[1]->type)->toBe(TokenType::INT)
                ->and($tokens[1]->value)->toBe(1000000);
        });

        it('should report error for invalid decimal format', function () {
            ob_start();
            $lexer = new Lexer('(12.3.4)', stopOnError: false);
            $lexer->tokenize();
            $output = ob_get_clean();

            expect($output)
                ->toContain('Invalid numeric literal')
                ->toContain('multiple decimal points detected');
        });
    });

    describe('Strings', function () {
        it('should tokenize single-quoted strings', function () {
            $lexer = new Lexer("('hello')", stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[1]->type)->toBe(TokenType::STRING)
                ->and($tokens[1]->value)->toBe('hello');
        });

        it('should tokenize double-quoted strings', function () {
            $lexer = new Lexer('("world")', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[1]->type)->toBe(TokenType::STRING)
                ->and($tokens[1]->value)->toBe('world');
        });

        it('should handle escape sequences in strings', function () {
            $lexer = new Lexer('("hello\\nworld")', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[1]->type)->toBe(TokenType::STRING)
                ->and($tokens[1]->value)->toBe("hello\nworld");
        });

        it('should report error for unterminated strings', function () {
            ob_start();
            $lexer = new Lexer('("unterminated)', stopOnError: false);
            $lexer->tokenize();
            $output = ob_get_clean();

            expect($output)
                ->toContain('Unterminated string literal')
                ->toContain('missing closing quote');
        });
    });

    describe('Identifiers', function () {
        it('should tokenize valid identifiers', function () {
            $lexer = new Lexer('(my_var_123)', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[1]->type)->toBe(TokenType::IDENTIFIER)
                ->and($tokens[1]->value)->toBe('my_var_123');
        });

        it('should tokenize identifiers starting with underscore', function () {
            $lexer = new Lexer('(_hidden)', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(4)
                ->and($tokens[1]->type)->toBe(TokenType::IDENTIFIER)
                ->and($tokens[1]->value)->toBe('_hidden');
        });
    });

    describe('Edge Cases', function () {
        it('should handle empty input', function () {
            $lexer = new Lexer('', stopOnError: false);
            $tokens = $lexer->tokenize();

            expect($tokens)->toHaveCount(1)
                ->and($tokens[0]->type)->toBe(TokenType::EOF);
        });

        it('should report error for top-level expressions not wrapped in parentheses', function () {
            ob_start();
            $lexer = new Lexer('show 123', stopOnError: false);
            $lexer->tokenize();
            $output = ob_get_clean();

            expect($output)
                ->toContain('Top-level expressions must be wrapped in parentheses');
        });

        it('should report error for unclosed parentheses', function () {
            ob_start();
            $lexer = new Lexer('((show 123)', stopOnError: false);
            $lexer->tokenize();
            $output = ob_get_clean();

            expect($output)
                ->toContain('Unclosed expression')
                ->toContain('maybe you forgot to close parentheses');
        });

        it('should report error for unexpected characters', function () {
            ob_start();
            $lexer = new Lexer('(show @)', stopOnError: false);
            $lexer->tokenize();
            $output = ob_get_clean();

            expect($output)
                ->toContain("Unexpected character '@'")
                ->toContain('invalid syntax');
        });
    });
});
