<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Installphp extends Base
{
	public function getName()
	{
		return 'install-php';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [version] - Установка php интерпретатора';
	}
}
