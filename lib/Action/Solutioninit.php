<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Solutioninit extends Base
{
	public function getName()
	{
		return 'solution-init';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Клонирует список модулей решения' . "\n"
			. Shell::getDisplayEnvVariable('SOLUTION_GIT_REPOS', true);
	}

	public function run()
	{
		$solution = $this->params[0] ?? '';

		if ($solution == '')
		{
			echo "Не указано решение.\n";
			return;
		}

		$solutionConfigPath = Shell::getHome() . '/bin/.solution.settings/' . $solution . '/example.env';

		if (!file_exists($solutionConfigPath))
		{
			echo "Конфигурация решения $solutionConfigPath не найдена.\n";
			return;
		}

		$siteConfig = $this->siteRootPath . '/.env';
		if (Shell::fixConfig($siteConfig, file_get_contents($solutionConfigPath)))
		{
			$newValue = Shell::loadFromEnv($siteConfig, 'SOLUTION_GIT_REPOS');
			$this->git->setParam('SOLUTION_GIT_REPOS', $newValue);
		}

		$this->git->fetchRepos();
	}
}