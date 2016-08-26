# Contributing To Administrator

- [Introduction](#introduction)
- [Bugs, Questions, and Feature Requests](#issues)
- [Pull Requests](#pull-requests)
- [Style Guide](#style-guide)
- [Debug and Logging](#debugging)

<a name="introduction"></a>
## Introduction

Administrator's source is hosted on [GitHub](https://github.com/ddpro/admin). It's distributed under the MIT license, so you are free to fork it and do whatever you like with it. In fact it started as another project also under the MIT license, [FrozenNode's Laravel-Administrator](https://github.com/FrozenNode/Laravel-Administrator/)

<a name="issues"></a>
## Bugs, Questions, and Feature Requests

If you've found a bug with Administrator, if you have a question, or if you have a feature request, the best way to get our attention is to post an issue on the [GitHub issue tracker](https://github.com/ddpro/admin/issues).

<a name="pull-requests"></a>
## Pull Requests

We love it when people submit pull requests. They don't always get merged into the core, but they almost always make us think about what is possible with Administrator and whether or not our current approach is adequate. If you'd like to submit a pull request, there are a few things that you should do in order to ensure a timely response:

- Fork from the `master` branch.

- Merge the latest changes from the `master` branch before you submit the pull request. If you have a request that can't be automatically merged, you may be asked to marge the latest changes and resubmit it.

- Add documentation for your changes to the relevant section in the `/docs` directory.

- Add any necessary unit tests

- Follow the [style guide](/src/style-guide.md)!

<a name="style-guide"></a>
## Style Guide

Please see the [style guide](/src/style-guide.md) page for more information about the style guide.

<a name="debugging"></a>
## Debug and Logging

I am using my own [applog](https://github.com/delatbabel/applog) package to provide slightly improved debug and and logging in comparison to out-of-the-box Laravel.  There are a few points to note.

### Correct Log Statement Format

The correct log statement format is like this:

```php
    Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
        'Some message goes here', $data);
```

`$data` is an optional array that contains data that will be converted to JSON format and stored along with the log entry.

Of course you can substitute `debug` with one of the other log levels, such as `info`, `error`, etc.

### Log Output

The log output is written to the normal location (storage/logs) as well as to the database table `applogs`.

Log output is not written to either log location if the log level is `debug` and if the `APP_DEBUG` environment variable is set to `false`.  Set this variable to `false` in production to disable debug logging.

### Log Facade

To use the Laravel log facade, use this use statement:

```php
    use Log;
```

... and not the full use statement which is:

```php
    use Illuminate\Support\Facades\Log;
```

This pulls in the Log facade by its alias -- in a Laravel application the Log facade is aliased to `Log`.  In a package test environment the facade is not available and so in the test cases you will see this alias instead:

```php
    // Stub out the log facade
    if (! class_exists('Log')) {
        class_alias('LogStub', 'Log');
    }
```

... this stubs out the log facade by aliasing the `Log` alias to a class called `LogStub` which is defined in the test harness and essentially no-ops all logging calls.  This prevents errors caused by uninitialised facades, and has the side effect of disabling logging while running package level unit tests.
