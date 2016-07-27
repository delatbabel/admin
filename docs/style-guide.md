# Style Guide

- [Introduction](#introduction)
- [Inline Comments](#inline-comments)
- [Function/Method Comments](#function-method-comments)
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

	//in order to ensure readability, we comment our code
	$code = Comment::my_code();

Always make sure there are line breaks *above* inline comments so they're easier to spot!

<a name="function-method-comments"></a>
## Function/Method Comments

Function or method comments should be in this form:

	/**
	 * Description of the method goes here. It can be as long as you need it to be.
	 *
	 * @param string		$someString
	 * @param int			$someInt
	 *
	 * @return false|array
	 */
	public function myMethod($someString, $someInt)
	{
		if (true)
		{
			return array('yay');
		}
		else
		{
			return false;
		}
	}

If there are no params, you can just put a single line between the description and the `@return`. If there is no return value, you can just include the description.

<a name="trimming-whitespace"></a>
## Trimming Whitespace

When possible, you should trim all whitespace at the end of lines. Many IDEs have a feature like this. In SublimeText, for example, you can add...

	"trim_trailing_white_space_on_save": true

...to your user settings.