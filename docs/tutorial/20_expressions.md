---
title: Parsing Expression Languages
---

Can Parsica parse expression? Why yes, I'm glad you asked!

An expression, roughly, is anything that can evaluated to a value, such as 

- arithmetic expressions  `(1 + 2) * 3`, 
- boolean expressions `x and (y or z)`,
- code inside a template language `{{ user.loggedIn ? 'Hello '  ~ user.name : 'Log in' }}`,
- spreadsheet formulas `=SUM(A1:A10) * B1`, 
- rules in a rule engine
- logic inside a configuration language,  
- and anything else you can think of! 

The tricky thing about parsing expressions is that you often have to deal with things like recursion, associativity, and operator precedence. These can make it pretty tricky to build a parser. Parsica provides the `expression()` function, which offers a simple way to create a parser for your custom expression language. 

## Arithmetic

Let's build a simple calculator, that can evaluate expressions like `1 + 2 * (2 - 3)` to `-1`. 

Let's handle whitespace first. (See the chapter on "Dealing with Space" for details.)

```php
<?php
$token = fn(Parser $parser) => keepFirst($parser, skipHSpace());
```

Next, we define a parser for our terms. For this example, let's keep it simple and support only natural numbers:

```php
<?php
$term = fn(): Parser => $token(atLeastOne(digitChar()))->map('intval');
```

Let's do parentheses next. Parsica's `between()` combinator will do the job nicely, but let's wrap it in our combinator for clarity and reusability:

```php
<?php
$parens = fn (Parser $parser): Parser =>  $token(between($token(char('(')), $token(char(')')), $parser));
```


Now let's define our first expression, using `expression()`. In our language, an expression can be:

1. A naked term like `12`
2. A term between parentheses `(12)`
3. An operator and its arguments `1 + 2`
4. The arguments are expressions themselves, as in `1 + (2 + 3)` 

An expression is defined using expressions, so this calls for recursion. (See the chapter on Recursion.) Let's ignore operators for now, and do the simplest recursive expression parser:

```php
<?php
$expr = recursive();
$primary = $parens($expr)->or($term());
$expr->recurse(
    expression($primary, [])
);

$result = $expr->tryString("(((12)))");
assertSame(12, $result->output());
```

We're saying here that `$primary` is either an expression wrapped in parens, or a term. `$expr` is an expression that uses `$primary` as its primary parser.

Now let's add the plus operator. We need a parser for the symbol itself, in this case a simple `char('+')` will do, but it could be anything. For example, PHP has two 'not equal' operators, which we could parse in one go `either(string('!='), string('<>'))`. 

We also need to decide what to do with the terms that we parse, using a transformation. This is a function that will take the left and the right operands from our `+`. As we're building a calculator, we're simple going to add up the two terms, using `fn($left, $right) => $left + $right`. (Later we will use this to create abstract syntax trees.)

Finally, we need to tell the expression parser that `+` is a binary operator, and that we want it to be left associative. Let's put it all together:

```php
<?php
$expr = recursive();
$primary = $parens($expr)->or($term());
$expr->recurse(
    expression(
        $primary,
        [
            leftAssoc(
                binaryOperator($token(char('+')), fn($l, $r) => $l + $r)
            )
        ]
    )
);

$result = $expr->tryString("1 + 2 + 3");
assertSame(6, $result->output());
$result = $expr->tryString("(1 + (2 + 3) + 4)");
assertSame(10, $result->output());
```

The second argument to `expression()` is an array of operators. The order is important: it determines the precedence. `+` and `-` have the same precedence, whereas `*` and `/` have the same precedence as each other, but higher precedence than `+` and `-`. We can solve this easily by grouping each precedence level, and putting the highest precedence levels first.  

```php
<?php
$expr = recursive();
$primary = $parens($expr)->or($term());
$expr->recurse(
    expression(
        $primary,
        [
            leftAssoc(
                binaryOperator($token(char('*')), fn($l, $r) => $l * $r),
                binaryOperator($token(char('/')), fn($l, $r) => $l / $r),
            ),
            leftAssoc(
                binaryOperator($token(char('+')), fn($l, $r) => $l + $r),
                binaryOperator($token(char('-')), fn($l, $r) => $l - $r),
            ),
        ]
    )
);

$result = $expr->tryString("1 + 2 * 3");
assertSame(7, $result->output());
$result = $expr->tryString("(1 + 2) * 3");
assertSame(9, $result->output());
$result = $expr->tryString("1 - 2 - 3"); // interpreted as ((1 - 2) - 3)
assertSame(-4, $result->output());
```

You can play around with the precedence and the associativity to see how it impacts the result. As an exercise, make a parser that solves `1 - 2 - 3 = (1 - (2 - 3) = (1 - (-1)) = 2`.

## Non-associative operators 

Non-associative means that an expression like `1 + 2 + 3` cannot be resolved, because there is no way to decide whether it's associates left `(1 + 2) + 3` or right `1 + (2 + 3)`. The parser will simply fail. Of course, for addition, non-associativity wouldn't make sense, but for other languages or operators it might.  

## Unary operators

You can add unary operators, such as the negation prefix operator `-`, and the increment and decrement postfix operators `++` and `--`.

```php
// ...
    [
        prefix(
            unaryOperator(char('-'), fn($v) => -$v)
        ),
        postfix(
            unaryOperator(string('++'), fn($v) => $v + 1),
            unaryOperator(string('--'), fn($v) => $v - 1),
        ),
        // ...
    ];

```

## Parsing to an AST

Building calculators isn't that interesting of course. Typically you'll want your parser to output a datastructure that represents your expression, called an Abstract Syntax Tree or AST. This structure can then be used for whatever the next step in your program is, ranging from evaluation to compilation, static analysis, typechecking, optimisation, rendering and formatting...

Let's build a simple Boolean expression language, starting with the types for AST. Everything else will be pretty similar to the calculator example above, but instead of evaluating the expressions on the fly, we use the transform functions to create the datastructure.

```php
<?php
// every term or expression in our language is a Boolean: 
interface Boolean {}

// Literals
class True_ implements Boolean {}
class False_ implements Boolean {}

// A variable will be replaced with a value at evaluation stage
class Variable implements Boolean {
    private string $name;
    function __construct(string $name){$this->name = $name;}
}

// Our operators are Booleans that are composed of other Booleans 
class Not_ implements Boolean {
    private Boolean $boolean;
    function __construct(Boolean $boolean){$this->boolean = $boolean;}
}
class And_ implements Boolean {
    private Boolean $l, $r;
    function __construct(Boolean $l, Boolean $r){
        $this->l = $l;
        $this->r = $r;
    }
}
class Or_ implements Boolean {
    private Boolean $l, $r;
    function __construct(Boolean $l, Boolean $r){
        $this->l = $l;
        $this->r = $r;
    }
}

// Now let's write the parser
$token = fn(Parser $parser) : Parser => keepFirst($parser, skipHSpace());
$parens = fn (Parser $parser): Parser =>  $token(between($token(char('(')), $token(char(')')), $parser));

// A term is a literal TRUE/FALSE or a variable 
$term = fn(): Parser => $token(choice(
    char('$')->followedBy(atLeastOne(alphaChar()))->map(fn($name) => new Variable($name)),
    string("TRUE")->map(fn($v) => new True_),
    string("FALSE")->map(fn($v) => new False_),
));
$expr = recursive();

// When the parser encounters NOT, AND, or OR, it returns a Not_, And_, or Or_ object.
// The $v, $l and $r arguments can be Boolean objects themselves, creating the tree. 
$expr->recurse(expression(
    $parens($expr)->or($term()),
    [
        prefix(
            unaryOperator($token(string("NOT")), fn($v) => new Not_($v))
        ),
        leftAssoc(
            binaryOperator($token(string("AND")), fn($l, $r) => new And_($l, $r))
        ),
        leftAssoc(
            binaryOperator($token(string("OR")), fn($l, $r) => new Or_($l, $r))
        ),
    ]
));


$parser = $expr->thenEof(); // check if we reached the end of the input
$result = $parser->tryString('$isBlue AND NOT ($isEdible OR $isDrinkable)');
assertEquals(
    new And_(
        new Variable('isBlue'),
        new Not_(
            new Or_(
                new Variable('isEdible'),
                new Variable('isDrinkable'),
            )
        )
    ),
    $result->output()
);
```

Now the AST can be used for whatever purposes you need. In our Boolean example above, as an exercise you can 

- add a `render()` method to write the expression back to a pretty formatted string, 
- add a `reduce()` method that simplifies the AST (eg turning `TRUE AND TRUE` into `TRUE`),
- add an `evaluate(['isBlue' => true, 'isEdible' => false, ...])` method that calculates the final result
- ...




