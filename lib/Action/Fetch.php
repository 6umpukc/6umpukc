<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Fetch extends Base
{
	public function getName()
	{
		return 'fetch';
	}

	protected function getVariants()
	{
		$result = [];
		foreach ($_SERVER as $k => $v)
		{
			if (!str_starts_with($k, 'BITRIX_SRC_'))
			{
				continue;
			}

			$result[mb_strtolower(mb_substr($k, 11))] = $v;
		}

		return $result;
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [edition] - Скачать указанную версию bitrix или скрипты' . "\n"
			. Shell::getDisplayVariants($this->getVariants());
	}
}


