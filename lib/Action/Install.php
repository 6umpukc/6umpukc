<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Install extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Установка bx в домашней директории пользователя';
	}
}
