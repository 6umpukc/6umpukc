<?php

namespace Rodzeta\Siteoptions\Shell;

use Rodzeta\Siteoptions\Shell;

final class Win
{
	public static function run($command, &$output)
	{
		$command = str_replace('&', '^&', $command);
		$cmd = 'cmd.exe /c ' . $command . ' 2>/dev/null';

		Shell::printCommand($cmd);

		return exec($cmd, $output);
	}

	public static function cscript($arguments = [])
	{
		if (empty($arguments))
		{
			return;
		}

		$args = '"' . implode('" "', $arguments) . '"';
		$cmd = 'cscript.exe ' . $args;
		$cmd = str_replace('&', '^&', $cmd);

		Shell::printCommand($cmd);

		ob_start();
		$result = Shell::run($cmd . ' 2>/dev/null');
		ob_end_clean();

		return $result;
	}

	public static function getEnvVariable($name)
	{
		$result = '';

		if (trim($name) == '')
		{
			return $result;
		}

		$result = trim(static::run('echo "%' . $name . '%"', $output));

		return $result;
	}
}