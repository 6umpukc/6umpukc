<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Upgrade extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Обновление системы.';
	}
}
