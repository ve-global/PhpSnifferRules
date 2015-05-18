<?php

namespace Vendor\Package;

use whatever\foo;

/**
 * Class comment
 */
abstract class ClassName
{
	const CONSTANT = '12';

	/**
	 * @var type
	 */
	private $variable;

	/**
	 * @var type
	 */
	protected static $foo;

	/**
	 * Whatever
	 */
	abstract protected function zim();

	/**
	 * Whatever
	 *
	 * @return type
	 */
	final public static function bar()
	{
		switch ($expr)
		{
			case 0:
				echo 'First case, with a break';
				break;
			case 1:
				
				echo 'Second case, which falls through';
				// no break
			case 2:
			case 3:
			case 4:
				echo 'Third case, return instead of break';
				return false;
			default:
				echo 'Default case';
				break;
		}

		try
		{
			// try body
		}
		catch (FirstExceptionType $e)
		{
			// catch body
		}
		catch (OtherExceptionType $e)
		{
			// catch body
		}

		do
		{
			// body
		}
		while (false);

		$this->{'whatever'}();

		$array = [12];
	}
}
