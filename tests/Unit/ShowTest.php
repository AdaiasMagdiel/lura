<?php

use AdaiasMagdiel\Lura\Lexer;
use AdaiasMagdiel\Lura\Parser;
use AdaiasMagdiel\Lura\Token\TokenType;

test('can outputs integers', function () {
    $program = "(show 1)";

    ob_start();
    $lexer = new Lexer($program, false);
    $tokens = $lexer->tokenize();

    $parser = new Parser($program, $tokens, false);
    $res = $parser->evaluate();
    $output = ob_get_clean();

    expect($res)
        ->toHaveCount(1)
        ->and($res[0]->type)
        ->toBe(TokenType::NIL);

    expect($output)
        ->toContain("1");
});

test('can outputs floats', function () {
    $program = "(show 3.14)";

    ob_start();
    $lexer = new Lexer($program, false);
    $tokens = $lexer->tokenize();

    $parser = new Parser($program, $tokens, false);
    $res = $parser->evaluate();
    $output = ob_get_clean();

    expect($res)
        ->toHaveCount(1)
        ->and($res[0]->type)
        ->toBe(TokenType::NIL);

    expect($output)
        ->toContain("3.14");
});
