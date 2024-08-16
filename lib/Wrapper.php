<?php

namespace Rodzeta\Siteoptions;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

class Wrapper extends Base
{
	//TODO!!! ??? use PATH in env for setup env variables

	const PATH_VARIABLES = [
		'SOLUTION_PHP_BIN' => '',
		'SOLUTION_NODE_BIN' => '',
	];

	public function getName()
	{
		return $this->name;
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Запустить ' . $this->getName() . ' с указанными настройками и версией';
	}

	public function run()
	{
		$pathVariables = $this->initPathFromEnv();

		$actionName = $this->getName();
		$uCommand = mb_strtoupper($actionName);
		if (!empty($pathVariables['SOLUTION_' . $uCommand . '_BIN']))
		{
			$path = '"' . $pathVariables['SOLUTION_' . $uCommand . '_BIN'] . '"';
		}
		else
		{
			$path = $actionName;
		}

		if (!empty($_SERVER['SOLUTION_' . $uCommand . '_ARGS']))
		{
			$args = Shell::getValues($_SERVER['SOLUTION_' . $uCommand . '_ARGS']);
			$path .= ' ' . implode(' ', $args);
		}

		$cmd = $path . ' ' . implode(' ', $this->params);

		if (!Shell::checkCommand($actionName))
		{
			echo "Action [$actionName] not defined\n";
			return;
		}

		Shell::run($cmd);
	}

	protected function initPathFromEnv()
	{
		$variables = static::PATH_VARIABLES;
		$newPath = '';
		foreach ($variables as $name => $v)
		{
			$path = $_SERVER[$name] ?? '';
			if ($path == '')
			{
				continue;
			}

			$path = Shell::getReplacedEnvVariables($path);
			if (Shell::isWSL())
			{
				$path = Shell::convertToWinPath($path);
			}

			$variables[$name] = $path;

			$sep = ':';
			$newPath .= dirname($path) . $sep;
		}

		if ($newPath != '')
		{
			$newPath .= $_SERVER['PATH'];
			Shell::updateEnv('PATH', $newPath);
		}

		/* TODO!!!  set env for shared-libs and path (for linux)
		var phpBinDir = p.dirname(phpBin);
		ENV_LOCAL['LD_LIBRARY_PATH'] = p.dirname(phpBinDir) + '/shared-libs';
		ENV_LOCAL['PATH'] = phpBinDir + ENV_PATH_SEP + PATH_ORIGINAL;
		*/

		return $variables;
	}
}
