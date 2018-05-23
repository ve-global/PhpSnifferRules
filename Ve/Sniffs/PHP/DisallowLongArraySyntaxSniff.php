<?php

namespace Ve\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * This sniff prohibits the use of the old array() syntax.
 *
 * For example:
 *
 * <code>
 * $array = array(
 *     "foo" => "bar",
 *     "bar" => "foo",
 * );
 * </code>
 *
 * The short syntax should be used instead:
 *
 * <code>
 * $array = [
 *     "foo" => "bar",
 *     "bar" => "foo",
 * ];
 * </code>
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 * @author Jack Blower  <Jack@elvenspellmaker.co.uk>
 */
class DisallowLongArraySyntaxSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_ARRAY);
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param File $phpcsFile The file where the token was found.
     * @param int  $stackPtr  The position in the stack where
     *                        the token was found.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['content'] === 'array') {
            $error = 'Expected [], found %s';
            $data  = array(trim($tokens[$stackPtr]['content']));
            $phpcsFile->addError($error, $stackPtr, '', $data);
        }

    }

}
