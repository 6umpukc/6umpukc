<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Installjs extends Base
{
	public function getName()
	{
		return 'install-js';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [version] - Установка nodejs и утилит';
	}
}
