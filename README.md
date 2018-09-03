# PhpSnifferRules

This is an additional set of rules for the PHP Code Sniffer.

## Installation

This package should be installed through composer.

Inside your project ```composer.json``` file add the package to the require-dev list.

```
"require-dev": {
    "phpunit/phpunit": "4.5.*",
    "ve-interactive/php-sniffer-rules": "dev-master"
},
```

Because the project is currently inside a private repository the repository itself needs to be added to the composer file.

```
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:ve-interactive/PhpSnifferRules.git"
    }
],
```

You will than have to create a new ```ruleset.xml``` file in the root of your project

```
<?xml version="1.0"?>
<ruleset name="REDACTED">
    <description>REDACTED</description>

    <!-- Ve Standards -->
    <rule ref="vendor/ve-interactive/php-sniffer-rules/Ve/ruleset.xml"/>

</ruleset>
```

If you want you can disable some of the rules that come with the package and enable other ones:

```
<?xml version="1.0"?>
<ruleset name="REDACTED">
    <description>REDACTED</description>

    <!-- Ve Standards -->
    <rule ref="vendor/ve-interactive/php-sniffer-rules/Ve/ruleset.xml">
        <!-- remove rule you don't want -->
        <exclude name="Generic.WhiteSpace.DisallowSpaceIndent"/>
    </rule>
    
    <!-- add rule you need -->
    <rule name="Generic.WhiteSpace.DisallowTabIndent"/>
    
    <!-- do not check this file at all -->
    <exclude-pattern>*/Redacted.php</exclude-pattern>

</ruleset>
```

## Usage

To run the phpcs tests just execute the following command inside your terminal

```
vendor/bin/phpcs --standard=phpcs.xml SrcDirectory/
```


## Rules Details

This ruleset extends the PSR2 standard with a few tweaks: 

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
