<?php

namespace Ve\Sniffs\WhiteSpaces;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Verifies that the negate operator is always followed by a space.
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 * @author Jack Blower  <Jack@elvenspellmaker.co.uk>
 */
class SpaceAfterNegateOperatorSniff implements Sniff
{

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_VARIABLE, T_STRING, T_ISSET);

    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param File $phpcsFile The file where the token was found.
     * @param int  $stackPtr  The position in the stack where the token was
     *                        found.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr - 1]['content'] === '!') {
            $phpcsFile->addError('Missing space after the "!"', $stackPtr, '');
        } elseif ($tokens[$stackPtr - 1]['content'] === '(' && $tokens[$stackPtr - 2]['content'] == '!') {
            $phpcsFile->addError('Missing space after the "!"', $stackPtr, '');
        }
    }

}
