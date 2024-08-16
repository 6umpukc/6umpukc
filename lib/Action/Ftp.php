<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Ftp extends Base
{
	public function getName()
	{
		return 'ftp';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Подключится по ftp (через filezilla)';
	}
}
