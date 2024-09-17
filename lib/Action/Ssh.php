<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Ssh extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Подключится по ssh';
	}
}
