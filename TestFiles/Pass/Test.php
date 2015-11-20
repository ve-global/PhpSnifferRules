<?php

namespace Vendor\Package;

use FooInterface;
use BarClass as Bar;
use OtherVendor\OtherPackage\BazClass;

/**
 * Class comment
 */
class Test extends Bar implements FooInterface
{
	/**
	 * Some text
	 *
	 * @var type
	 */
	public $fooBar = null;

	/**
	 * Some text
	 *
	 * @param string  $a
	 * @param boolean $b
	 */
	public function sampleFunction($a, $b = false)
	{
		if ($a === $b)
		{
			bar();
		}
		elseif ($a > $b)
		{
			$foo->bar($arg1);
		}
		else
		{
			BazClass::bar($arg2, $arg3);
		}

		if (! $b)
		{
			echo $c;
		}

		if (! strtok($b))
		{
			echo $d;
		}

		if (! isset($b))
		{
			echo $d;
		}

		if (! ($var === $b))
		{
			echo $c;
		}

		$variableName = 'foo';

		$fn = function() {

		};
	}

	/**
	 * Whatever.
	 */
	final public static function bar()
	{
		// method body
	}
}
