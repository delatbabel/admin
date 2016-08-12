# Style Guide

- [Introduction](#introduction)
- [Inline Comments](#inline-comments)
- [Function/Method Docblocks](#function-method-docblocks)
- [Trimming Whitespace](#trimming-whitespace)

<a name="introduction"></a>
## Introduction

We use [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) as the basic coding standard.

We use [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) as the autoloading standard.

If you are using [Sensio Labs' PSR fixer](http://cs.sensiolabs.org/) then these are the options I use:

```bash
php-cs-fixer fix $DIR --level=psr2 --fixers=extra_empty_lines,duplicate_semicolon,operators_spaces,spaces_before_semicolon,whitespacy_lines,align_double_arrow,align_equals,concat_with_spaces,logical_not_operators_with_successor_space,newline_after_open_tag,ordered_use
```

<a name="inline-comments"></a>
## Inline Comments

You should pepper your code with inline comments as much as possible without being overly verbose. Ideally you want to make it so that someone reading through the code the first time has a reasonable chance at understanding it. The correct type of comment should be the double-slash like this:

    // in order to ensure readability, we comment our code
    $code = Comment::my_code();

Always make sure there are line breaks *above* inline comments so they're easier to spot!  Do not put a line break below the inline comment (between the inline comment and the code that it is commenting), but put one after the code that is being commented, between that and any further code that does not relate to the comment above it.  Here is an example:

    // Add two numbers together
    $i = $a + $b;
    
    print $x; // this line has nothing to do with adding two numbers together so there is a blank line before it.

<a name="function-method-docblocks"></a>
## Function/Method Docblocks

Function or method docblocks should be in this form, so that they can be easily interpreted by apigen.

    /**
     * Header for the method goes here.  It can be one line only.
     *
     * Description of the method goes here. It can be as long as you need it to be.
     *
     * @param string        $someString
     * @param int           $someInt
     *
     * @return false|array
     */
    public function myMethod($someString, $someInt)
    {
        if (true) {
            return array('yay');
        } else {
            return false;
        }
    }

If there are no params, you can just put a single line between the description and the `@return`. If there is no return value, you can just include the description.

<a name="trimming-whitespace"></a>
## Trimming Whitespace

When possible, you should trim all whitespace at the end of lines. Many IDEs have a feature like this. In SublimeText, for example, you can add...

    "trim_trailing_white_space_on_save": true

...to your user settings.
