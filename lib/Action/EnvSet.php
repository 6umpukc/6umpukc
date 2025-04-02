<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class EnvSet extends Base
{
	public function getName()
	{
		return 'env-set';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Переключится на указанное окружение';
	}
}
