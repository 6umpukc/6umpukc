<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Reset extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Сбросить изменения по git-репозитариям решения' . "\n"
		. Shell::getDisplayEnvVariable('SOLUTION_GIT_REPOS', true);
	}

	public function run()
	{
		if (!Shell::confirm('Warning! All file changes will be removed.'))
		{
			return;
		}

		foreach ($this->git->iterateRepos() as $repoInfo)
		{
			Shell::run('pwd');
			Shell::run('git reset --hard HEAD');
			Shell::run('git clean -f -d');
		}
	}
}
