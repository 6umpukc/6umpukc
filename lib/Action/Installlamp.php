<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Installlamp extends Base
{
	public function getName()
	{
		return 'install-lamp';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Установка LAMP';
	}
}
