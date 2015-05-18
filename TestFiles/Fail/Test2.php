<?php

namespace Vendor\Package;

use whatever\foo;

abstract class class_Name
{
	const consTant = '12';

	private $_variable;

	static protected $foo;

	public $foo_bar;

	protected abstract function zim();

	final public static function bar()
	{
		switch ($expr) {
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
			return;
			default:
				echo 'Default case';
				break;
		}

		try {
			// try body
		} catch(FirstExceptionType $e) {
			// catch body
		}
		catch (OtherExceptionType $e)
		{
			// catch body
		}

		do {
			// body
		} while (false);

		$this->{'whatever'}();

        $array = array(12);
	}
}
?>