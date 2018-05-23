<?php

namespace Ve\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks if the interfaces contain either the suffix or prefix.
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 * @author Jack Blower  <Jack@elvenspellmaker.co.uk>
 */
class InterfaceClassNameSniff implements Sniff
{
	const SUFFIX = 'Interface';
	const PREFIX = 'I';

	public $checkSuffix = false;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_INTERFACE,
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens  = $phpcsFile->getTokens();
        $className = $tokens[$phpcsFile->findNext(T_STRING, $stackPtr)]['content'];

		if ($this->checkSuffix && strpos($className, self::SUFFIX) !== mb_strlen($className) - mb_strlen(self::SUFFIX))
		{
			$phpcsFile->addError('The interface "' . $className . '" does not have the "' . self::SUFFIX . '" suffix in its name.', $stackPtr, 'MissingSuffix');
		}
		elseif (! $this->checkSuffix && strpos($className, self::PREFIX) !== 0)
		{
			$phpcsFile->addError('The interface "' . $className . '" does not have the "' . self::PREFIX . '" prefix in its name.', $stackPtr, 'MissingPrefix');
		}
    }

}
