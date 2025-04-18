<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Example extends Base
{
	public function run()
	{
		echo 'Action ' . $this->getName()
			. ' defined in ' . static::class
			. PHP_EOL;
	}
}
