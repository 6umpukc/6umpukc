<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Es6 extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' . - Транспиляция *.es6.js файлов в текущей директории' //. "\n"
			// ??? . 'bx ' . $this->getName() . ' - Транспиляция js модулей решения'
			;
	}
}
