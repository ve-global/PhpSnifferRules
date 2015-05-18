<?php

/**
 * Ensure single and multi-line function declarations are defined correctly.
 *
 * @author    Nicola Puddu <nicola.puddu@veinteractive.com>
 */
class Ve_Sniffs_Functions_MultiLineFunctionDeclarationSniff extends Squiz_Sniffs_Functions_MultiLineFunctionDeclarationSniff
{

   /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
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
        }//end if

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
            }//end if
        }//end if

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

    }//end process()


}//end class