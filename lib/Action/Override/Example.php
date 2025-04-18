<?php

namespace Rodzeta\Siteoptions\Action\Override;

use Rodzeta\Siteoptions\Base;

//return;

final class Example extends Base
{
	public function run()
	{
		echo 'Action ' . $this->getName()
			. ' overrided by ' . static::class
			. PHP_EOL;
	}
}
