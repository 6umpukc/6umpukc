<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Start extends Base
{
	public function getName()
	{
		return 'start';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Запустить сервисы LAMP и т.д.';
	}
}
