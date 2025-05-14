<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Gitbackup extends Base
{
	public function getName()
	{
		return 'git-backup';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Скопировать и заархивировать git репозитарии по списку' . "\n"
			. Shell::getDisplayEnvVariable('BX_BACKUP_GIT_REPOS_DEST')
			. Shell::getDisplayEnvVariable('BX_BACKUP_GIT_REPOS_DEST_ARCHIVED')
			. Shell::getDisplayEnvVariable('BX_BACKUP_GIT_REPOS');
	}

	public function run()
	{
		$repos = Shell::getValues($_SERVER['BX_BACKUP_GIT_REPOS'] ?? '');
		$destPath = Shell::getReplacedEnvVariables($_SERVER['BX_BACKUP_GIT_REPOS_DEST'] ?? '');
		$destPathArchived = Shell::getReplacedEnvVariables(
			$_SERVER['BX_BACKUP_GIT_REPOS_DEST_ARCHIVED'] ?? ($destPath . '/.archived/'));

		if (empty($destPath))
		{
			return;
		}

		if (!is_dir($destPath))
		{
			mkdir($destPath);
		}
		if (!is_dir($destPathArchived))
		{
			mkdir($destPathArchived, 0777, true);
		}

		chdir($destPath);
		echo getcwd() . "\n";

		foreach ($repos as $repo)
		{
			echo "Clone $repo ...\n";

			$name = $this->git->getName($repo);
			$path = $destPath . '/' . $name;

			if (str_starts_with($name, '.'))
			{
				$fname = '_' . mb_substr($name, 1);
			}
			else
			{
				$fname = $name;
			}
			$fname .= '.tar';
			$archivePath = $destPathArchived . $fname;

			chdir($destPath);

			if (is_dir($path))
			{
				chdir($path);
				Shell::runGetContent('git pull', $resultGitPull);

				if ($this->isActualStatus($resultGitPull))
				{
					if (file_exists($archivePath))
					{
						continue;
					}
				}
			}
			else
			{
				chdir($destPath);
				Shell::run('git clone ' . $repo);
				//Shell::run('git clone --mirror ' . $repo . ' ' . $path);
			}

			Shell::tarCreate($archivePath, $path);

			echo "\n";
		}
	}

	protected function isActualStatus($resultGitPull)
	{
		return (mb_strpos($resultGitPull, 'Already up to date') !== false)
			|| (mb_strpos($resultGitPull, 'Уже актуально') !== false);
	}
}
