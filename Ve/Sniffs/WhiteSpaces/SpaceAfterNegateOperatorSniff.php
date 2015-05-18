<?php

/**
 * Verifies that the negate operator is always followed by a space.
 *
 * @author    Nicola Puddu <nicola.puddu@veinteractive.com>
 */
class Ve_Sniffs_WhiteSpaces_SpaceAfterNegateOperatorSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_VARIABLE, T_STRING, T_ISSET);

    }//end register()

	/**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
		if ($tokens[$stackPtr -1]['content'] == '!') {
			$phpcsFile->addError('Missing space after the "!"', $stackPtr);
		} elseif ($tokens[$stackPtr -1]['content'] == '(' && $tokens[$stackPtr -2]['content'] == '!') {
			$phpcsFile->addError('Missing space after the "!"', $stackPtr);
		}
    }//end process()



}//end class