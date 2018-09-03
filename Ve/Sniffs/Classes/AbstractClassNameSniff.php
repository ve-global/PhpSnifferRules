<?php

/**
 * Checks if the abstract class name has the Abstract prefix.
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 */
class Ve_Sniffs_Classes_AbstractClassNameSniff implements PHP_CodeSniffer_Sniff
{
	const PREFIX = 'Abstract';

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_CLASS,
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens  = $phpcsFile->getTokens();
        $className = $tokens[$phpcsFile->findNext(T_STRING, $stackPtr)]['content'];

		if ($tokens[$stackPtr -2]['code'] === T_ABSTRACT && strpos($className, self::PREFIX) !== 0)
		{
			$phpcsFile->addError('The abstract class "' . $className . '" does not have the "' . self::PREFIX . '" prefix in its name.', $stackPtr, 'WrongName');
		}
    }

}
