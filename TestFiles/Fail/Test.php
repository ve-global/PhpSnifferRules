<?php

namespace Vendor\Package;
use FooInterface;
use BarClass as Bar;
use OtherVendor\OtherPackage\BazClass;
class Foo extends Bar implements FooInterface
{
	public $foo_bar = null;

	public function SampleFunction($a, $b = NULL)
	{
		if ( $a === $b) {
			bar();
		} elseif ($a > $b) {
			$foo->bar($arg1);
		} else {
			BazClass::bar($arg2, $arg3);
		}

		if (!$b)
		{
			echo $c;
		}

		if (!strtok($b))
		{
			echo $d;
		}

		if (!isset($b))
		{
			echo $d;
		}

		if (!($var === $b))
		{
			echo $c;
		}

		$variable_name = 'foo';

		$_fn = function () {

		};
	}

	static public final function bar_foo()
	{
		// method body
	}

	function test() {}

	private function _wrong()
	{

	}

	/**
	 * @param boolean $b
	 */
	public function testBoolean($b)
	{

	}

	/**
	 * @param boolean $b
	 */
	public function testBoolean2(boolean $b)
	{

	}

	/**
	 * @param mixed ...$test
	 * @param mixed $test2
	 */
	public function variadic($test, ...$test2)
	{

	}

	/**
	 * @param mixed $test
	 * @param mixed &$test2
	 */
	public function reference(&$test, $test2)
	{

	}

	/**
	 * @param stdClass[] $whatever
	 */
	public function arrayType($whatever)
	{

	}
}
