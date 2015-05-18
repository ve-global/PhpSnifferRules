# PhpSnifferRules

This is an additional set of rules for the PHP Code Sniffer.

## Installation

installation instructions.

```
<?xml version="1.0"?>
<ruleset name="REDACTED">
    <description>REDACTED</description>

    <!-- Ve Standards -->
    <rule ref="vendor/ve-interactive/php-sniffer-rules/Ve/ruleset.xml"/>

</ruleset>
```


## Rules Details

This ruleset extends the PSR2 standard with a few twicks: 

- arrays must be declared using the short syntax.
- tabs must be used in place of spaces.
- the ! operator must always be followed by an empty space
- control structures curly brackets must always be in a new line.
- all variables must be camel case.
- doc blocks must be used for every single CLASS, METHOD and ATTRIBUTE

## Tests

The package comes with a few test files.
Running the following command should return no errors:

```
vendor/bin/phpcs --standard=Ve/ruleset.xml TestFiles/Pass/
```

Running this command will instead return several errors.

```
vendor/bin/phpcs --standard=Ve/ruleset.xml TestFiles/Fail/
```
