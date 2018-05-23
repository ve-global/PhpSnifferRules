<?php

namespace Ve\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Verifies that control statements conform to their coding standards.
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 * @author Jack Blower  <Jack@elvenspellmaker.co.uk>
 */
class ControlSignatureSniff implements Sniff
{
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
		'PHP',
		'JS',
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return int[]
	 */
	public function register()
	{
		return array(
			T_TRY,
			T_CATCH,
			T_DO,
			T_WHILE,
			T_FOR,
			T_IF,
			T_FOREACH,
			T_ELSE,
			T_ELSEIF,
		);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the
	 *                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		$this->checkSingleSpaceAfterKeyword($tokens, $stackPtr, $phpcsFile);
		$this->checkNewLineAfterClosingParenthesis($tokens, $stackPtr, $phpcsFile);
		$this->checkNewLineAfterOpeningBrace($tokens, $stackPtr, $phpcsFile);

		// Only want to check multi-keyword structures from here on.
		if ($tokens[$stackPtr]['code'] === T_TRY || $tokens[$stackPtr]['code'] === T_DO
		)
		{
			$closer = $tokens[$stackPtr]['scope_closer'];
		}
		else if ($tokens[$stackPtr]['code'] === T_ELSE || $tokens[$stackPtr]['code'] === T_ELSEIF
		)
		{
			$closer = $phpcsFile->findPrevious(Tokens::$emptyTokens,
				($stackPtr - 1), null, true);
			if ($closer === false || $tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET)
			{
				return;
			}
		}
		else
		{
			return;
		}

		// Single new line after closing brace.

		$this->checkNewLineAfterPointer($tokens, $closer, 'closing brace', $phpcsFile);
	}

	/**
	 * @param array   $tokens
	 * @param integer $stackPtr
	 * @param File    $phpcsFile
	 */
	private function checkSingleSpaceAfterKeyword(array $tokens, $stackPtr,
											   File $phpcsFile)
	{
		if (in_array($tokens[$stackPtr]['code'], [T_TRY, T_DO, T_ELSE]))
		{
			$this->checkNewLineAfterPointer($tokens, $stackPtr,
				strtoupper($tokens[$stackPtr]['content']).' keyword', $phpcsFile);
		}
		else
		{
			$found = 1;
			if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE)
			{

				$found = 0;
			}
			else if ($tokens[($stackPtr + 1)]['content'] !== ' ')
			{
				if (strpos($tokens[($stackPtr + 1)]['content'], $phpcsFile->eolChar) !== false)
				{
					$found = 'newline';
				}
				else
				{
					$found = strlen($tokens[($stackPtr + 1)]['content']);
				}
			}

			if ($found !== 1)
			{
				$error	 = 'Expected 1 space after %s keyword; %s found';
				$data	 = array(
					strtoupper($tokens[$stackPtr]['content']),
					$found,
				);

				$fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword',
					$data);
				if ($fix === true)
				{
					if ($found === 0)
					{
						$phpcsFile->fixer->addContent($stackPtr, ' ');
					}
					else
					{
						$phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
					}
				}
			}
		}
	}

	/**
	 * @param array   $tokens
	 * @param integer $stackPtr
	 * @param File    $phpcsFile
	 */
	private function checkNewLineAfterClosingParenthesis(array $tokens, $stackPtr,
													  File $phpcsFile)
	{
		if (isset($tokens[$stackPtr]['parenthesis_closer']) === true && isset($tokens[$stackPtr]['scope_opener'])
			=== true
		)
		{
			$closer = $tokens[$stackPtr]['parenthesis_closer'];
			$this->checkNewLineAfterPointer($tokens, $closer, 'closing parenthesis',
				$phpcsFile);
		}
	}

	/**
	 * @param array $tokens
	 * @param integer $stackPtr
	 * @param File $phpcsFile
	 */
	private function checkNewLineAfterOpeningBrace(array $tokens, $stackPtr,
												$phpcsFile)
	{
		if (isset($tokens[$stackPtr]['scope_opener']) === true)
		{
			$opener = $tokens[$stackPtr]['scope_opener'];

			$this->checkNewLineAfterPointer($tokens, $opener, 'opening brace', $phpcsFile);
		}
		else if ($tokens[$stackPtr]['code'] === T_WHILE)
		{
			// Zero spaces after parenthesis closer.
			$closer	 = $tokens[$stackPtr]['parenthesis_closer'];
			$found	 = 0;
			if ($tokens[($closer + 1)]['code'] === T_WHITESPACE)
			{
				if (strpos($tokens[($closer + 1)]['content'], $phpcsFile->eolChar) !== false)
				{
					$found = 'newline';
				}
				else
				{
					$found = strlen($tokens[($closer + 1)]['content']);
				}
			}

			if ($found !== 0)
			{
				$error	 = 'Expected 0 spaces before semicolon; %s found';
				$data	 = array($found);
				$fix	 = $phpcsFile->addFixableError($error, $closer, 'SpaceBeforeSemicolon',
					$data);
				if ($fix === true)
				{
					$phpcsFile->fixer->replaceToken(($closer + 1), '');
				}
			}
		}
	}

	/**
	 * @param array $tokens
	 * @param integer $pointer
	 * @param string $pointerName
	 * @param File $phpcsFile
	 */
	private function checkNewLineAfterPointer(array $tokens, $pointer,
										   $pointerName, File $phpcsFile)
	{
		for ($next = ($pointer + 1); $next < $phpcsFile->numTokens; $next++)
		{
			$code = $tokens[$next]['code'];

			// Skip all whitespace.
			if ($code === T_WHITESPACE)
			{
				continue;
			}

			// Skip all empty tokens on the same line as the opener.
			if ($tokens[$next]['line'] === $tokens[$pointer]['line'] && isset(Tokens::$emptyTokens[$code])
				=== true
			)
			{
				continue;
			}

			// We found the first bit of a code, or a comment on the
			// following line.
			break;
		}

		$found = ($tokens[$next]['line'] - $tokens[$pointer]['line']);
		if ($found !== 1)
		{
			$error	 = 'Expected 1 newline after '.$pointerName.'; %s found';
			$data	 = array($found);
			$fix	 = $phpcsFile->addFixableError($error, $pointer,
				'NewLineAfterControlPointer', $data);
			if ($fix === true)
			{
				$phpcsFile->fixer->beginChangeset();
				for ($i = ($pointer + 1); $i < $next; $i++)
				{
					if ($found > 0 && $tokens[$i]['line'] === $tokens[$next]['line'])
					{
						break;
					}

					$phpcsFile->fixer->replaceToken($i, '');
				}

				$phpcsFile->fixer->addContent($pointer, $phpcsFile->eolChar);
				$phpcsFile->fixer->endChangeset();
			}
		}
	}
}
