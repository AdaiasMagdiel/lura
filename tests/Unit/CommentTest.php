<?php

use AdaiasMagdiel\Lura\Lexer;
use AdaiasMagdiel\Lura\Token\TokenType;

test('single line comments are ignored', function () {
    $lexer = new Lexer("# this comment should produce no tokens", stopOnError: false);
    $tokens = $lexer->tokenize();

    expect($tokens)
        ->toHaveCount(1)
        ->and($tokens[0]->type)
        ->toBe(TokenType::EOF);
});

test('line comments terminate at newline', function () {
    $lexer = new Lexer(
        <<<CODE
            # this is a comment
            ()
            CODE,
        stopOnError: false
    );

    $tokens = $lexer->tokenize();

    expect($tokens)->toHaveCount(3)
        ->and($tokens[0]->type)->toBe(TokenType::L_PAREN)
        ->and($tokens[1]->type)->toBe(TokenType::R_PAREN)
        ->and($tokens[2]->type)->toBe(TokenType::EOF);
});

test('multiline comments are properly parsed', function () {
    $lexer = new Lexer(
        <<<CODE
            #- This comment
            spans multiple
            lines -#
            CODE,
        stopOnError: false
    );

    $tokens = $lexer->tokenize();

    expect($tokens)
        ->toHaveCount(1)
        ->and($tokens[0]->type)
        ->toBe(TokenType::EOF);
});

test('multiline comments wtesth code inside are ignored', function () {
    $lexer = new Lexer(
        <<<CODE
            #- (show "this should be ignored") -#
            CODE,
        stopOnError: false
    );

    $tokens = $lexer->tokenize();

    expect($tokens)
        ->toHaveCount(1)
        ->and($tokens[0]->type)
        ->toBe(TokenType::EOF);
});

test('unclosed multiline comments trigger syntax error', function () {
    ob_start();

    $lexer = new Lexer(
        <<<CODE
            #- This comment never closes
            (show "this should fail")
            CODE,
        stopOnError: false
    );

    $lexer->tokenize();
    $output = ob_get_clean();

    expect($output)
        ->toContain("Unterminated block comment")
        ->and($output)
        ->toContain("expected '-#' to close");
});
