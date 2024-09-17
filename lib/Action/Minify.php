<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Minify extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' . - Минификация js и css в текущей директории' . "\n"
			. 'bx ' . $this->getName() . ' - Минификация js и css модулей решения';
	}
}
