# Lura

Lura is a lightweight, Lisp-inspired programming language named as an anagram of "Raul", in tribute to the legendary Brazilian singer [Raul Seixas](https://en.wikipedia.org/wiki/Raul_Seixas) ([Spotify](https://open.spotify.com/intl-pt/artist/7jrRQZg4FZq6dwpi3baKcu)). While Lura adopts a Lisp-like syntax with parentheses, it is not a full Lisp dialect, focusing instead on simplicity and a minimalistic design for educational and experimental purposes.

## Features

- **Lisp-like Syntax**: Code is structured using parentheses, with expressions evaluated in a nested, functional style.
- **Basic Types**: Supports integers, floating-point numbers, strings, and identifiers.
- **Flexible Commenting**: Includes single-line and multi-line comments.
- **Console Output**: Built-in functions `show` and `echo` for printing with or without newlines.
- **Arithmetic and String Operations**: Supports addition (`+`) and string concatenation (`concat`).
- **Error Handling**: Robust lexer and parser with detailed syntax error reporting.

## Language Overview

### Comments

Lura supports both single-line and multi-line comments for code documentation.

```lura
# This is a single-line comment

#-
This is a
multi-line
comment
-#
```

### Primitive Types

Lura supports the following primitive data types:

```lura
(42)          # Integer
(3.14)        # Float
("Hello")     # String
('World')     # String (single quotes)
(concat)      # Identifier (function name)
```

- **Integers and Floats**: Numeric literals can include underscores for readability (e.g., `1_000`) and decimals for floats (e.g., `3.14`).
- **Strings**: Can be enclosed in single (`'`) or double (`"`) quotes, with support for escape sequences like `\n` (newline), `\t` (tab), and `\\` (backslash).

### Console Output

Lura provides two built-in functions for console output:

```lura
(show "Hello, world!")  # Prints "Hello, world!" with a newline
(echo "Hello, world!")  # Prints "Hello, world!" without a newline
```

### Arithmetic Operations

The `+` operator supports addition for integers and floats:

```lura
(show (+ 1 2))        # Prints: 3
(show (+ 1.5 2.5))    # Prints: 4.0
(show (+ 1 2 3 4))    # Prints: 10
```

### String Concatenation

The `concat` function combines two or more strings:

```lura
(show (concat "Hello, " "world!"))  # Prints: Hello, world!
(show (concat "A" "B" "C"))         # Prints: ABC
```

### Syntax Rules

- **Parentheses**: All expressions must be enclosed in parentheses, even at the top level.
- **Identifiers**: Function names and other identifiers must start with a letter or underscore and can include alphanumeric characters and underscores.
- **Error Handling**: The lexer and parser provide detailed error messages for invalid syntax, unterminated strings, unclosed parentheses, and undefined identifiers.

### Example Program

Here’s a sample Lura program demonstrating various features:

```lura
(show "Welcome to Lura!")                # Prints: Welcome to Lura!
(show (+ 10 20 30))                     # Prints: 60
(show (concat "Hello, " "Raul" "!"))    # Prints: Hello, Raul!
(echo "No newline here")                # Prints: No newline here
(show "This is on a new line")          # Prints: This is on a new line
```

## How It Works

### Lexer

The lexer (implemented in `Lexer.php`) tokenizes input code into a stream of tokens, including:

- **Token Types**: `INT`, `FLOAT`, `STRING`, `IDENTIFIER`, `L_PAREN`, `R_PAREN`, `PLUS`, `NIL`, and `EOF`.
- **Features**:
  - Handles numeric literals with underscores (e.g., `1_000`) and decimal points for floats.
  - Supports single- and double-quoted strings with escape sequences.
  - Recognizes single-line (`#`) and multi-line (`#- ... -#`) comments.
  - Tracks line and column numbers for precise error reporting.
  - Enforces parentheses for top-level expressions.

### Parser

The parser (implemented in `Parser.php`) processes the token stream to evaluate expressions:

- **Expression Parsing**: Supports atomic expressions (numbers, strings, identifiers) and lists (parenthesized expressions).
- **Function Calls**: Uses a `Builtins` class to handle function calls like `show`, `echo`, `+`, and `concat`.
- **Error Handling**: Reports errors for undefined identifiers and invalid syntax, ensuring robust feedback.

## License

Lura is licensed under the GNU General Public License v3.0 (GPL-3.0-only). See the [LICENSE](LICENSE) file for details.
