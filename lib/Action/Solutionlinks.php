<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Solutionlinks extends Base
{
	public function getName()
	{
		return 'solution-links';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Создает символьные ссылки для модулей решения' . "\n"
			. Shell::getDisplayEnvVariable('SOLUTION_GIT_REPOS', true);
	}
}
