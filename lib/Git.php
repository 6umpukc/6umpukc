<?php

namespace Rodzeta\Siteoptions;

use DirectoryIterator;
use Rodzeta\Siteoptions\Shell;

final class Git
{
	const SOLUTION_REPOS_SEP = ';';

	protected $siteRootPath;
	protected $params;

	public function __construct($siteRootPath = null, $params = [])
	{
		$this->siteRootPath = $siteRootPath ?: getcwd();
		$this->params = $params;
	}

	public function getBitrixModulesPath()
	{
		return $this->siteRootPath . '/bitrix/modules/';
	}

	public function setParam($key, $value)
	{
		$this->params[$key] = $value;

		return $this->params[$key];
	}

	public function getMapRepos()
	{
		$pathModules = $this->getBitrixModulesPath();
		$solutionRepos = Shell::getValues($this->params['SOLUTION_GIT_REPOS']);

		// not bitrix site = dir with repos
		if (!is_dir($pathModules))
		{
			$solutionRepos = [];
			foreach (new DirectoryIterator($this->siteRootPath) as $f)
			{
				if (!$f->isDot() && $f->isDir())
				{
					$solutionRepos[] = $f->getPathname() . ';master;' . $f->getBasename();
				}
			}
		}

		$result = [];
		foreach ($solutionRepos as $line)
		{
			if (trim($line) == '')
			{
				continue;
			}

			$tmp = explode(static::SOLUTION_REPOS_SEP, $line);

			$url = trim($tmp[0]);
			$moduleId = basename($url, '.git');
			$branch = (count($tmp) > 1)? trim($tmp[1]) : 'master';

			if (!is_dir($pathModules))
			{
				$path = ($this->siteRootPath . '/' . (trim($tmp[2]) ?: $moduleId));
			}
			else
			{
				$path = $pathModules . $moduleId;
			}

			$page = (count($tmp) > 3) ? trim($tmp[3]) : '';

			$result[] = [
				'moduleId' => $moduleId,
				'url' => $url,
				'branch' => $branch,
				'path' => $path,
				'page' => $page,
			];
		}

		return $result;
	}

	public function fetchRepos()
	{
		$solutionRepos = $this->getMapRepos();

		if (count($solutionRepos) == 0)
		{
			return;
		}

		$pathModules = $this->getBitrixModulesPath();
		if (!is_dir($pathModules))
		{
			mkdir($pathModules, 0777, true);
		}

		echo "Repositories info:\n";
		foreach ($solutionRepos as $repoInfo)
		{
			echo $repoInfo['moduleId'] . ' [' . $repoInfo['branch'] . ']' . "\n";
			echo "\t" . $repoInfo['url'] . "\n";
			echo "\t\t-> " . $repoInfo['path'] . "\n\n";
		}

		if (!Shell::confirm('Warning! Modules will be removed.'))
		{
			return;
		}

		foreach ($solutionRepos as $repoInfo)
		{
			echo 'Fetch repo ' . $repoInfo['url'] . " ...\n";
			$this->clone(
				$repoInfo['url'],
				$repoInfo['branch'],
				$repoInfo['path']
			);

			$this->addToGitignore(str_replace($this->siteRootPath, '', $repoInfo['path']));
		}
	}

	protected function addToGitignore($path)
	{
		Shell::fixConfig($this->siteRootPath . '/.gitignore', $path);
	}

	public function clone($url, $branch, $path)
	{
		if (is_dir($path))
		{
			Shell::run('rm -Rf ' . $path);
		}

		Shell::run('git clone ' . $url . ' ' . $path);
		if (is_dir($path))
		{
			chdir($path);
			Shell::run('git config core.fileMode false');
			Shell::run('git checkout ' . $branch);
			Shell::run('git config --global --add safe.directory ' . $path);
		}

		chdir($this->siteRootPath);
	}

	public function iterateRepos()
	{
		$solutionRepos = $this->getMapRepos();
		if (count($solutionRepos) == 0)
		{
			return;
		}

		foreach ($solutionRepos as $repoInfo)
		{
			if (!is_dir($repoInfo['path']))
			{
				echo 'Directory ' . $repoInfo['path'] . ' for ' . $repoInfo['url'] . ' not exists' . "\n";
				continue;
			}

			chdir($repoInfo['path']);

			if (!is_dir($repoInfo['path'] . '/.git/'))
			{
				continue;
			}

			echo "\n";

			yield $repoInfo;

			echo "\n";
		}
	}
}