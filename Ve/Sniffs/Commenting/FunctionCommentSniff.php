<?php
if (class_exists('PEAR_Sniffs_Commenting_FunctionCommentSniff', true) === false)
{
	throw new PHP_CodeSniffer_Exception('Class PEAR_Sniffs_Commenting_FunctionCommentSniff not found');
}

/**
 * Parses and verifies the doc comments for functions.
 *
 * @author Nicola Puddu <nicola.puddu@veinteractive.com>
 */
class Ve_Sniffs_Commenting_FunctionCommentSniff extends Squiz_Sniffs_Commenting_FunctionCommentSniff
{

	/**
	 * Process the function parameter comments.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile    The file being scanned.
	 * @param int                  $stackPtr     The position of the current token
	 *                                           in the stack passed in $tokens.
	 * @param int                  $commentStart The position in the stack where the comment started.
	 *
	 * @return void
	 */
	protected function processParams(PHP_CodeSniffer_File $phpcsFile, $stackPtr,
								  $commentStart)
	{
		$tokens = $phpcsFile->getTokens();

		$params	 = array();
		$maxType = 0;
		$maxVar	 = 0;
		foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag)
		{
			if ($tokens[$tag]['content'] !== '@param')
			{
				continue;
			}

			$type			 = '';
			$typeSpace		 = 0;
			$var			 = '';
			$varSpace		 = 0;
			$comment		 = '';
			$commentLines	 = array();
			if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING)
			{
				$matches	 = array();
				preg_match('/([^$&.]+)(?:((?:\$|&|.)[^\s]+)(?:(\s+)(.*))?)?/',
					$tokens[($tag + 2)]['content'], $matches);
				$typeLen	 = strlen($matches[1]);
				$type		 = trim($matches[1]);
				$typeSpace	 = ($typeLen - strlen($type));
				$typeLen	 = strlen($type);
				if ($typeLen > $maxType)
				{
					$maxType = $typeLen;
				}

				if (isset($matches[2]) === true)
				{
					$var	 = $matches[2];
					$varLen	 = strlen($var);
					if ($varLen > $maxVar)
					{
						$maxVar = $varLen;
					}

					if (isset($matches[4]) === true)
					{
						$varSpace		 = strlen($matches[3]);
						$comment		 = $matches[4];
						$commentLines[]	 = array(
							'comment' => $comment,
							'token' => ($tag + 2),
							'indent' => $varSpace,
						);

						// Any strings until the next tag belong to this comment.
						if (isset($tokens[$commentStart]['comment_tags'][($pos + 1)]) === true)
						{
							$end = $tokens[$commentStart]['comment_tags'][($pos + 1)];
						}
						else
						{
							$end = $tokens[$commentStart]['comment_closer'];
						}

						for ($i = ($tag + 3); $i < $end; $i++)
						{
							if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING)
							{
								$indent = 0;
								if ($tokens[($i - 1)]['code'] === T_DOC_COMMENT_WHITESPACE)
								{
									$indent = strlen($tokens[($i - 1)]['content']);
								}

								$comment .= ' '.$tokens[$i]['content'];
								$commentLines[] = array(
									'comment' => $tokens[$i]['content'],
									'token' => $i,
									'indent' => $indent,
								);
							}
						}
					}
					else
					{
						$error			 = 'Missing parameter comment';
						$phpcsFile->addError($error, $tag, 'MissingParamComment');
						$commentLines[]	 = array('comment' => '');
					}
				}
				else
				{
					$error = 'Missing parameter name';
					$phpcsFile->addError($error, $tag, 'MissingParamName');
				}
			}
			else
			{
				$error = 'Missing parameter type';
				$phpcsFile->addError($error, $tag, 'MissingParamType');
			}

			$params[] = array(
				'tag' => $tag,
				'type' => $type,
				'var' => $var,
				'comment' => $comment,
				'commentLines' => $commentLines,
				'type_space' => $typeSpace,
				'var_space' => $varSpace,
			);
		}

		$realParams	 = $this->getMethodParametersWithVariadic($stackPtr, $phpcsFile,
			$tokens);
		$foundParams = array();

		foreach ($params as $pos => $param)
		{
			// If the type is empty, the whole line is empty.
			if ($param['type'] === '')
			{
				continue;
			}

			// Check the param type value.
			$typeNames = explode('|', $param['type']);
			foreach ($typeNames as $typeName)
			{
				$suggestedName = PHP_CodeSniffer::suggestType($typeName);
				if ($typeName !== $suggestedName)
				{
					$error	 = 'Expected "%s" but found "%s" for parameter type';
					$data	 = array(
						$suggestedName,
						$typeName,
					);

					$fix = $phpcsFile->addFixableError($error, $param['tag'],
						'IncorrectParamVarName', $data);
					if ($fix === true)
					{
						$content = $suggestedName;
						$content .= str_repeat(' ', $param['type_space']);
						$content .= $param['var'];
						$content .= str_repeat(' ', $param['var_space']);
						if (isset($param['commentLines'][0]) === true)
						{
							$content .= $param['commentLines'][0]['comment'];
						}

						$phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);
					}
				}
				else if (count($typeNames) === 1)
				{
					// Check type hint for array and custom type.
					$suggestedTypeHint = '';
					if (strpos($suggestedName, 'array') !== false)
					{
						$suggestedTypeHint = 'array';
					}
					else if (strpos($suggestedName, 'callable') !== false)
					{
						$suggestedTypeHint = 'callable';
					}
					else if (in_array($typeName, PHP_CodeSniffer::$allowedTypes) === false)
					{
						$suggestedTypeHint = $suggestedName;
					}

					if ($suggestedTypeHint !== '' && isset($realParams[$pos]) === true)
					{
						$typeHint = $realParams[$pos]['type_hint'];
						if ($typeHint === '')
						{
							$error	 = 'Type hint "%s" missing for %s';
							$data	 = array(
								$suggestedTypeHint,
								$param['var'],
							);
							$phpcsFile->addError($error, $stackPtr, 'TypeHintMissing', $data);
						}
						else if ($typeHint !== substr($suggestedTypeHint, (strlen($typeHint) * -1)))
						{
							$error	 = 'Expected type hint "%s"; found "%s" for %s';
							$data	 = array(
								$suggestedTypeHint,
								$typeHint,
								$param['var'],
							);
							$phpcsFile->addError($error, $stackPtr, 'IncorrectTypeHint', $data);
						}
					}
					else if ($suggestedTypeHint === '' && isset($realParams[$pos]) === true)
					{
						$typeHint = $realParams[$pos]['type_hint'];
						if ($typeHint !== '')
						{
							$error	 = 'Unknown type hint "%s" found for %s';
							$data	 = array(
								$typeHint,
								$param['var'],
							);
							$phpcsFile->addError($error, $stackPtr, 'InvalidTypeHint', $data);
						}
					}
				}
			}

			if ($param['var'] === '')
			{
				continue;
			}

			$foundParams[] = $param['var'];

			// Check number of spaces after the type.
			$spaces = ($maxType - strlen($param['type']) + 1);
			if ($param['type_space'] !== $spaces)
			{
				$error	 = 'Expected %s spaces after parameter type; %s found';
				$data	 = array(
					$spaces,
					$param['type_space'],
				);

				$fix = $phpcsFile->addFixableError($error, $param['tag'],
					'SpacingAfterParamType', $data);
				if ($fix === true)
				{
					$phpcsFile->fixer->beginChangeset();

					$content = $param['type'];
					$content .= str_repeat(' ', $spaces);
					$content .= $param['var'];
					$content .= str_repeat(' ', $param['var_space']);
					$content .= $param['commentLines'][0]['comment'];
					$phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);

					// Fix up the indent of additional comment lines.
					foreach ($param['commentLines'] as $lineNum => $line)
					{
						if ($lineNum === 0 || $param['commentLines'][$lineNum]['indent'] === 0
						)
						{
							continue;
						}

						$newIndent = ($param['commentLines'][$lineNum]['indent'] + $spaces - $param['type_space']);
						$phpcsFile->fixer->replaceToken(
							($param['commentLines'][$lineNum]['token'] - 1),
							str_repeat(' ', $newIndent)
						);
					}

					$phpcsFile->fixer->endChangeset();
				}
			}
			// Make sure the param name is correct.
			if (isset($realParams[$pos]) === true)
			{
				$realName		 = $realParams[$pos]['name'];
				$isVariadic		 = $realParams[$pos]['is_variadic'];
				$passByReference = $realParams[$pos]['pass_by_reference'];
				if ($realName !== $param['var'] && !$isVariadic && !$passByReference)
				{
					$code	 = 'ParamNameNoMatch';
					$data	 = array(
						$param['var'],
						$realName,
					);

					$error = 'Doc comment for parameter %s does not match ';
					if (strtolower($param['var']) === strtolower($realName))
					{
						$error .= 'case of ';
						$code = 'ParamNameNoCaseMatch';
					}

					$error .= 'actual variable name %s';

					$phpcsFile->addError($error, $param['tag'], $code, $data);
				}
				else if ($isVariadic && '...'.$realName !== $param['var'])
				{
					$phpcsFile->addError('Doc comment for the parameter '.$realName.' should be variadic.',
						$param['tag'], 'NotVariadic');
				}
				else if ($passByReference && '&'.$realName !== $param['var'])
				{
					$phpcsFile->addError('Doc comment for the parameter '.$realName.' should contain the ampersand.',
						$param['tag'], 'NotByReference');
				}
			}
			else if (substr($param['var'], -4) !== ',...')
			{
				// We must have an extra parameter comment.
				$error = 'Superfluous parameter comment';
				$phpcsFile->addError($error, $param['tag'], 'ExtraParamComment');
			}

			if ($param['comment'] === '')
			{
				continue;
			}

			// Check number of spaces after the var name.
			$spaces = ($maxVar - strlen($param['var']) + 1);
			if ($param['var_space'] !== $spaces)
			{
				$error	 = 'Expected %s spaces after parameter name; %s found';
				$data	 = array(
					$spaces,
					$param['var_space'],
				);

				$fix = $phpcsFile->addFixableError($error, $param['tag'],
					'SpacingAfterParamName', $data);
				if ($fix === true)
				{
					$phpcsFile->fixer->beginChangeset();

					$content = $param['type'];
					$content .= str_repeat(' ', $param['type_space']);
					$content .= $param['var'];
					$content .= str_repeat(' ', $spaces);
					$content .= $param['commentLines'][0]['comment'];
					$phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);

					// Fix up the indent of additional comment lines.
					foreach ($param['commentLines'] as $lineNum => $line)
					{
						if ($lineNum === 0 || $param['commentLines'][$lineNum]['indent'] === 0
						)
						{
							continue;
						}

						$newIndent = ($param['commentLines'][$lineNum]['indent'] + $spaces - $param['var_space']);
						$phpcsFile->fixer->replaceToken(
							($param['commentLines'][$lineNum]['token'] - 1),
							str_repeat(' ', $newIndent)
						);
					}

					$phpcsFile->fixer->endChangeset();
				}
			}
			// Param comments must start with a capital letter and end with the full stop.
			$firstChar = $param['comment']{0};
			if (preg_match('|\p{Lu}|u', $firstChar) === 0)
			{
				$error = 'Parameter comment must start with a capital letter';
				$phpcsFile->addError($error, $param['tag'], 'ParamCommentNotCapital');
			}

			$lastChar = substr($param['comment'], -1);
			if ($lastChar !== '.')
			{
				$error = 'Parameter comment must end with a full stop';
				$phpcsFile->addError($error, $param['tag'], 'ParamCommentFullStop');
			}
		}

		$realNames = array();
		foreach ($realParams as $realParam)
		{
			$realNames[] = $realParam['name'];
		}

		// Report missing comments.
		$diff = array_diff($realNames, $foundParams);
		foreach ($diff as $neededParam)
		{
			$error	 = 'Doc comment for parameter "%s" missing';
			$data	 = array($neededParam);
			$phpcsFile->addError($error, $commentStart, 'MissingParamTag', $data);
		}
	}

	/**
	 * Returns the method parameters for the specified T_FUNCTION token.
	 *
	 * Each parameter is in the following format:
	 *
	 * <code>
	 *   0 => array(
	 *         'name'              => '$var',  // The variable name.
	 *         'pass_by_reference' => false,   // Passed by reference.
	 *         'is_variadic'       => false,   // Is variadic.
	 *         'type_hint'         => string,  // Type hint for array or custom type
	 *        )
	 * </code>
	 *
	 * Parameters with default values have an additional array index of
	 * 'default' with the value of the default as a string.
	 *
	 * @param int $stackPtr The position in the stack of the T_FUNCTION token
	 *                      to acquire the parameters for.
	 *
	 * @return array
	 * @throws PHP_CodeSniffer_Exception If the specified $stackPtr is not of
	 *                                   type T_FUNCTION.
	 */
	private function getMethodParametersWithVariadic($stackPtr, $phpcsFile, $tokens)
	{
		if ($tokens[$stackPtr]['code'] !== T_FUNCTION)
		{
			throw new PHP_CodeSniffer_Exception('$stackPtr must be of type T_FUNCTION');
		}

		$opener	 = $tokens[$stackPtr]['parenthesis_opener'];
		$closer	 = $tokens[$stackPtr]['parenthesis_closer'];

		$vars			 = array();
		$currVar		 = null;
		$defaultStart	 = null;
		$paramCount		 = 0;
		$passByReference = false;
		$isVariadic		 = false;
		$typeHint		 = '';

		for ($i = ($opener + 1); $i <= $closer; $i++)
		{
			// Check to see if this token has a parenthesis opener. If it does
			// its likely to be an array, which might have arguments in it, which
			// we cause problems in our parsing below, so lets just skip to the
			// end of it.
			if (isset($tokens[$i]['parenthesis_opener']) === true)
			{
				// Don't do this if it's the close parenthesis for the method.
				if ($i !== $tokens[$i]['parenthesis_closer'])
				{
					$i = ($tokens[$i]['parenthesis_closer'] + 1);
				}
			}

			switch ($tokens[$i]['code'])
			{
				case T_BITWISE_AND:
					$passByReference = true;
					break;
				case T_ELLIPSIS:
					$isVariadic		 = true;
					break;
				case T_VARIABLE:
					$currVar		 = $i;
					break;
				case T_ARRAY_HINT:
				case T_CALLABLE:
					$typeHint		 = $tokens[$i]['content'];
					break;
				case T_STRING:
					// This is a string, so it may be a type hint, but it could
					// also be a constant used as a default value.
					$prevComma		 = false;
					for ($t = $i; $t >= $opener; $t--)
					{
						if ($tokens[$t]['code'] === T_COMMA)
						{
							$prevComma = $t;
							break;
						}
					}

					if ($prevComma !== false)
					{
						$nextEquals = false;
						for ($t = $prevComma; $t < $i; $t++)
						{
							if ($tokens[$t]['code'] === T_EQUAL)
							{
								$nextEquals = $t;
								break;
							}
						}

						if ($nextEquals !== false)
						{
							break;
						}
					}

					if ($defaultStart === null)
					{
						$typeHint .= $tokens[$i]['content'];
					}
					break;
				case T_NS_SEPARATOR:
					// Part of a type hint or default value.
					if ($defaultStart === null)
					{
						$typeHint .= $tokens[$i]['content'];
					}
					break;
				case T_CLOSE_PARENTHESIS:
				case T_COMMA:
					// If it's null, then there must be no parameters for this
					// method.
					if ($currVar === null)
					{
						continue;
					}

					$vars[$paramCount]			 = array();
					$vars[$paramCount]['name']	 = $tokens[$currVar]['content'];

					if ($defaultStart !== null)
					{
						$vars[$paramCount]['default'] = $phpcsFile->getTokensAsString(
							$defaultStart, ($i - $defaultStart)
						);
					}

					$vars[$paramCount]['pass_by_reference']	 = $passByReference;
					$vars[$paramCount]['type_hint']			 = $typeHint;
					$vars[$paramCount]['is_variadic']		 = $isVariadic;

					// Reset the vars, as we are about to process the next parameter.
					$defaultStart	 = null;
					$passByReference = false;
					$isVariadic		 = false;
					$typeHint		 = '';

					$paramCount++;
					break;
				case T_EQUAL:
					$defaultStart = ($i + 1);
					break;
			}
		}

		return $vars;
	}
}
