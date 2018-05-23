<?php

namespace Ve\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Functions\MultiLineFunctionDeclarationSniff as OtherMultiLineFunctionDeclarationSniff;

/**
 * Ensure single and multi-line function declarations are defined correctly.
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 * @author Jack Blower  <Jack@elvenspellmaker.co.uk>
 */
class MultiLineFunctionDeclarationSniff extends OtherMultiLineFunctionDeclarationSniff
{
   /**
    * @var array
    */
    private $stopTokens = ['T_SEMICOLON', 'T_ABSTRACT', 'T_CLOSE_CURLY_BRACKET'];

   /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];

        if ($tokens[$stackPtr]['code'] === T_FUNCTION) {
            // Must be one space after the FUNCTION keyword.
            if ($tokens[($stackPtr + 1)]['content'] === $phpcsFile->eolChar) {
                $spaces = 'newline';
            } else if ($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE) {
                $spaces = strlen($tokens[($stackPtr + 1)]['content']);
            } else {
                $spaces = 0;
            }

            if ($spaces !== 1) {
                $error = 'Expected 1 space after FUNCTION keyword; %s found';
                $data  = array($spaces);
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterFunction', $data);
                if ($fix === true) {
                    if ($spaces === 0) {
                        $phpcsFile->fixer->addContent($stackPtr, ' ');
                    } else {
                        $phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
                    }
                }
            }
        } else {
            if ($tokens[($stackPtr + 1)]['content'] === $phpcsFile->eolChar) {
                $spaces = 'newline';
            } else if ($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE) {
                $spaces = strlen($tokens[($stackPtr + 1)]['content']);
            } else {
                $spaces = 0;
            }

            if ($spaces === 1) {
                $error = 'Expected 0 space after FUNCTION keyword on closure; %s found';
                $data  = array($spaces);
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterClosure', $data);
                if ($fix === true) {
                    if ($spaces === 0) {
                        $phpcsFile->fixer->addContent($stackPtr, ' ');
                    } else {
                        $phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
                    }
                }
            }
        }

        // Must be one space before the opening parenthesis. For closures, this is
        // enforced by the first check because there is no content between the keywords
        // and the opening parenthesis.
        if ($tokens[$stackPtr]['code'] === T_FUNCTION) {
            if ($tokens[($openBracket - 1)]['content'] === $phpcsFile->eolChar) {
                $spaces = 'newline';
            } else if ($tokens[($openBracket - 1)]['code'] === T_WHITESPACE) {
                $spaces = strlen($tokens[($openBracket - 1)]['content']);
            } else {
                $spaces = 0;
            }

            if ($spaces !== 0) {
                $error = 'Expected 0 spaces before opening parenthesis; %s found';
                $data  = array($spaces);
                $fix   = $phpcsFile->addFixableError($error, $openBracket, 'SpaceBeforeOpenParen', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken(($openBracket - 1), '');
                }
            }
        }

        // Must be one space before and after USE keyword for closures.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                if ($tokens[($use + 1)]['code'] !== T_WHITESPACE) {
                    $length = 0;
                } else if ($tokens[($use + 1)]['content'] === "\t") {
                    $length = '\t';
                } else {
                    $length = strlen($tokens[($use + 1)]['content']);
                }

                if ($length !== 1) {
                    $error = 'Expected 1 space after USE keyword; found %s';
                    $data  = array($length);
                    $fix   = $phpcsFile->addFixableError($error, $use, 'SpaceAfterUse', $data);
                    if ($fix === true) {
                        if ($length === 0) {
                            $phpcsFile->fixer->addContent($use, ' ');
                        } else {
                            $phpcsFile->fixer->replaceToken(($use + 1), ' ');
                        }
                    }
                }

                if ($tokens[($use - 1)]['code'] !== T_WHITESPACE) {
                    $length = 0;
                } else if ($tokens[($use - 1)]['content'] === "\t") {
                    $length = '\t';
                } else {
                    $length = strlen($tokens[($use - 1)]['content']);
                }

                if ($length !== 1) {
                    $error = 'Expected 1 space before USE keyword; found %s';
                    $data  = array($length);
                    $fix   = $phpcsFile->addFixableError($error, $use, 'SpaceBeforeUse', $data);
                    if ($fix === true) {
                        if ($length === 0) {
                            $phpcsFile->fixer->addContentBefore($use, ' ');
                        } else {
                            $phpcsFile->fixer->replaceToken(($use - 1), ' ');
                        }
                    }
                }
            }
        }

        // Check if this is a single line or multi-line declaration.
        $singleLine = true;
        if ($tokens[$openBracket]['line'] === $tokens[$closeBracket]['line']) {
            // Closures may use the USE keyword and so be multi-line in this way.
            if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
                if ($use !== false) {
                    // If the opening and closing parenthesis of the use statement
                    // are also on the same line, this is a single line declaration.
                    $open  = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
                    $close = $tokens[$open]['parenthesis_closer'];
                    if ($tokens[$open]['line'] !== $tokens[$close]['line']) {
                        $singleLine = false;
                    }
                }
            }
        } else {
            $singleLine = false;
        }

        if ($singleLine === true) {
            $this->processSingleLineDeclaration($phpcsFile, $stackPtr, $tokens);
        } else {
            $this->processMultiLineDeclaration($phpcsFile, $stackPtr, $tokens);
        }

    }

    /**
     * Override of the multiline sniffer to enforce new line after the closing parenthesis.
     *
     * @param File    $phpcsFile
     * @param integer $stackPtr
     * @param array   $tokens
     */
    public function processMultiLineDeclaration($phpcsFile, $stackPtr, $tokens)
    {
        // We need to work out how far indented the function
        // declaration itself is, so we can work out how far to
        // indent parameters.
        $functionIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($tokens[$i]['code'] === T_WHITESPACE) {
            $functionIndent = strlen($tokens[$i]['content']);
        }

        // The closing parenthesis must be on a new line, even
        // when checking abstract function definitions.
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        $prev         = $phpcsFile->findPrevious(
            T_WHITESPACE,
            ($closeBracket - 1),
            null,
            true
        );

        if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
            if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                $error = 'The closing parenthesis of a multi-line function declaration must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'CloseBracketLine');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($closeBracket);
                }
            }
        }

        // If this is a closure and is using a USE statement, the closing
        // parenthesis we need to look at from now on is the closing parenthesis
        // of the USE statement.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                $open         = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
                $closeBracket = $tokens[$open]['parenthesis_closer'];

                $prev = $phpcsFile->findPrevious(
                    T_WHITESPACE,
                    ($closeBracket - 1),
                    null,
                    true
                );

                if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
                    if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                        $error = 'The closing parenthesis of a multi-line use declaration must be on a new line';
                        $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'UseCloseBracketLine');
                        if ($fix === true) {
                            $phpcsFile->fixer->addNewlineBefore($closeBracket);
                        }
                    }
                }
            }
        }

        // Each line between the parenthesis should be indented 4 spaces.
        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $lastLine    = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($i === $tokens[$stackPtr]['parenthesis_closer']
                    || ($tokens[$i]['code'] === T_WHITESPACE
                    && (($i + 1) === $closeBracket
                    || ($i + 1) === $tokens[$stackPtr]['parenthesis_closer']))
                ) {
                    // Closing braces need to be indented to the same level
                    // as the function.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + $this->indent);
                }

                // We changed lines, so this should be a whitespace indent token.
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line function declaration not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                              $expectedIndent,
                              $foundIndent,
                             );

                    $fix = $phpcsFile->addFixableError($error, $i, 'Indent', $data);
                    if ($fix === true) {
                        $spaces = str_repeat(' ', $expectedIndent);
                        if ($foundIndent === 0) {
                            $phpcsFile->fixer->addContentBefore($i, $spaces);
                        } else {
                            $phpcsFile->fixer->replaceToken($i, $spaces);
                        }
                    }
                }

                $lastLine = $tokens[$i]['line'];
            }

            if ($tokens[$i]['code'] === T_ARRAY || $tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                // Skip arrays as they have their own indentation rules.
                if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                    $i = $tokens[$i]['bracket_closer'];
                } else {
                    $i = $tokens[$i]['parenthesis_closer'];
                }

                $lastLine = $tokens[$i]['line'];
                continue;
            }
        }

        if (isset($tokens[$stackPtr]['scope_opener']) === true) {
            // The opening brace needs to be one space away
            // from the closing parenthesis.
            $next = $tokens[($closeBracket + 1)];
            if ($next['code'] !== T_WHITESPACE) {
                $length = 0;
            } else if ($next['content'] === $phpcsFile->eolChar) {
                $length = -1;
            } else {
                $length = strlen($next['content']);
            }

            if ($length !== -1) {
                $phpcsFile->addError('There should be a new line after a closing parenthesis.', $closeBracket, 'SpaceAfterClosingParenthesis');

                return;
            }//end if

            // And just in case they do something funny before the brace...
            $next = $phpcsFile->findNext(
                T_WHITESPACE,
                ($closeBracket + 1),
                null,
                true
            );

            if ($next !== false && $tokens[$next]['code'] !== T_OPEN_CURLY_BRACKET) {
                $error = 'There must be a single space between the closing parenthesis and the opening brace of a multi-line function declaration';
                $phpcsFile->addError($error, $next, 'NoSpaceBeforeOpenBrace');
            }
        }

        // from parent

        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $this->processBracket($phpcsFile, $openBracket, $tokens, 'function');

        $this->checkOpenCurlyBrackets($phpcsFile, $stackPtr, $tokens, $functionIndent);

        if ($tokens[$stackPtr]['code'] !== T_CLOSURE) {
            return;
        }

        $use = $phpcsFile->findNext(T_USE, ($tokens[$stackPtr]['parenthesis_closer'] + 1), $tokens[$stackPtr]['scope_opener']);
        if ($use === false) {
            return;
        }

        $openBracket = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1), null);
        $this->processBracket($phpcsFile, $openBracket, $tokens, 'use');

        // Also check spacing.
        if ($tokens[($use - 1)]['code'] === T_WHITESPACE) {
            $gap = strlen($tokens[($use - 1)]['content']);
        } else {
            $gap = 0;
        }
    }

    /**
     * Checks the indentation of opening curly brackets
     *
     * @param File    $phpcsFile
     * @param integer $stackPtr
     * @param array   $tokens
     * @param integer $functionIndent
     */
    private function checkOpenCurlyBrackets(File $phpcsFile, $stackPtr, $tokens, $functionIndent)
    {
        $openCurlyBracket = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, ($tokens[$stackPtr]['parenthesis_closer'] + 1));
        $actualIndentation = $tokens[$openCurlyBracket]['column'] - 1;

        if ($functionIndent !== $actualIndentation) {
            $abstractPtr = $stackPtr;
            while (
                --$abstractPtr > 0
                && ! in_array($tokens[$abstractPtr]['type'], $this->stopTokens)
            );

            if ($tokens[$abstractPtr]['type'] !== 'T_ABSTRACT')
            {
                $phpcsFile->addError('There should be ' . $functionIndent . ' spaces before the opening bracket. ' . $actualIndentation . ' found.', $openCurlyBracket, 'OpeningBraketIndentation');
            }
        }

    }
}
