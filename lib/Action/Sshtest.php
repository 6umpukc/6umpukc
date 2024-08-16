<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Ssh extends Base
{
	public function getName()
	{
		return 'ssh-test';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Проверить подключение по ssh';
	}
}
