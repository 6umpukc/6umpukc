<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Checkout extends Base
{
	public function getName()
	{
		return 'checkout';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Переключится на ветку git-репозитария решения' . "\n"
		. Shell::getDisplayEnvVariable('SOLUTION_GIT_REPOS', true);
	}

	public function run()
	{
		foreach ($this->git->iterateRepos() as $repoInfo)
		{
			Shell::run('pwd');
			Shell::run('git checkout ' . $repoInfo['branch']);
		}
	}
}
