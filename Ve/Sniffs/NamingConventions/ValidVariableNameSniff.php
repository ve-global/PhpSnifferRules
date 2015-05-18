<?php

/**
 * Verifies that variable names are all camelCase.
 *
 * @author    Nicola Puddu <nicola.puddu@veinteractive.com>
 */
class Ve_Sniffs_NamingConventions_ValidVariableNameSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_VARIABLE);

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
		$variableName = $tokens[$stackPtr]['content'];
		if ($variableName{1} !== strtolower($variableName{1})) {
			$phpcsFile->addError('The first character of a variable name must be lowercase.', $stackPtr);
		}

		if (strpos($variableName, '_') !== false)
		{
			$phpcsFile->addError('Variable names must be camelCase.', $stackPtr);
		}
    }//end process()



}//end class