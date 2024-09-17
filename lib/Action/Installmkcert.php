<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Installmkcert extends Base
{
	public function getName()
	{
		return 'install-mkcert';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Установка Mkcert и создание сертификата для https (для локальной разработки)';
	}
}
