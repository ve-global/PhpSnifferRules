<?php

namespace Ve\Sniffs\NamingConventions;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Verifies that variable names are all camelCase.
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 * @author Jack Blower  <Jack@elvenspellmaker.co.uk>
 */
class ValidVariableNameSniff implements Sniff
{

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
	 */
	public function register()
	{
		return array(T_VARIABLE);

	}

	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		$variableName = $tokens[$stackPtr]['content'];
		if (in_array($variableName, ['$_SERVER', '$_GET', '$_POST', '$_COOKIE', '$_FILES', '$_ENV', '$_REQUEST', '$_SESSION'])) {
			return;
		}

		if ($variableName{1} !== strtolower($variableName{1})) {
			$phpcsFile->addError('The first character of a variable name must be lowercase.', $stackPtr, '');
		}

		if (strpos($variableName, '_') !== false)
		{
			$phpcsFile->addError('Variable names must be camelCase.', $stackPtr, '');
		}
	}

}
