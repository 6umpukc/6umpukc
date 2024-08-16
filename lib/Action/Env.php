<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Env extends Base
{
	public function getName()
	{
		return 'env';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Вывод переменных окружения по проекту';
	}
}
