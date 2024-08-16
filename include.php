<?php

namespace Rodzeta\Siteoptions;

use Throwable;

// php ~/bin/rodzeta.siteoptions/include.php [arg1] [arg2] [arg3]

function initBitrixCli()
{
	// автозагрузчик от bitrix используется только при явном указании опции
	if (empty($_SERVER['BX_USE_BITRIX']))
	{
		return false;
	}

	if (file_exists($_SERVER['DOCUMENT_ROOT']
			. '/bitrix/modules/main/cli/bootstrap.php'))
	{
		try
		{
			define('BX_BUFFER_USED', true);
			require $_SERVER['DOCUMENT_ROOT']
				. '/bitrix/modules/main/cli/bootstrap.php';

			return true;
		}
		catch (Throwable $e)
		{
		}
	}

	return false;
}

function runCommand($script, $siteRootPath, $params)
{
	$actionName = array_shift($params) ?? '';
	$actionName = str_replace('-', '', $actionName);
	if (empty($actionName))
	{
		return;
	}

	$className = '\\Rodzeta\\Siteoptions\\Action\\' . ucfirst($actionName);

	if (!class_exists($className))
	{
		$className = '\\Rodzeta\\Siteoptions\\Wrapper';

		$action = new $className($script, $siteRootPath, $params, $actionName);
	}
	else
	{
		$action = new $className($script, $siteRootPath, $params);
	}

	$action->run();
}

function runCli()
{
	$params = $_SERVER['argv'];
	$script = array_shift($params);
	$siteRootPath = array_shift($params);
	$_SERVER['DOCUMENT_ROOT'] = $siteRootPath;

	if (empty($_SERVER['DOCUMENT_ROOT']))
	{
		$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));
	}

	if (initBitrixCli())
	{
		//...
	}
	else
	{
		spl_autoload_register(function ($class) {
			$class = str_replace(__NAMESPACE__, '', $class);
			$class = str_replace('\\', '/', $class);
			$class = trim($class, '\/');

			$path = __DIR__ . '/lib/' . $class . '.php';
			if (file_exists($path))
			{
				require $path;
			}
		});
		//...
	}

	runCommand($script, $siteRootPath, $params);
}

function main()
{
	if (php_sapi_name() === 'cli')
	{
		runCli();
	}
}

main();
