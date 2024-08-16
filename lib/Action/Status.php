<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Status extends Base
{
	public function getName()
	{
		return 'status';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Проверить состояние по git-репозитариям решения' . "\n"
		. Shell::getDisplayEnvVariable('SOLUTION_GIT_REPOS', true);
	}

	public function run()
	{
		foreach ($this->git->iterateRepos() as $repoInfo)
		{
			Shell::run('pwd');
			Shell::run('git status');
			Shell::run('git branch');
		}
	}
}
