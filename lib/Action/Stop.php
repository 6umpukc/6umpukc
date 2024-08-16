<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Stop extends Base
{
	public function getName()
	{
		return 'stop';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Остановить сервисы LAMP и т.д.';
	}
}
