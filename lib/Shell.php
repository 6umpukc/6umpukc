<?php

namespace Rodzeta\Siteoptions;

final class Shell
{
	public static function getRealBinPath()
	{
		return dirname($_SERVER['argv'][0]);
	}

	public static function getUser()
	{
		return $_SERVER['USER'] ?? '';
	}

	public static function getHome()
	{
		return $_SERVER['HOME'] ?? '';
	}

	public static function confirm($title)
	{
		$line = readline("$title Type 'yes' to continue: ");

		return trim($line) == 'yes';
	}

	protected static function printCommand($cmd)
	{
		if (isset($_SERVER['BX_DEBUG']) && ($_SERVER['BX_DEBUG'] == '1'))
		{
			echo "\n";
			echo '$ ' . $cmd . "\n";
			echo "\n";
		}
	}

	public static function run($cmd)
	{
		static::printCommand($cmd);

		return system($cmd);
	}

	public static function runGetContent($cmd, &$output = null, $disableDebug = null)
	{
		if ($disableDebug !== true)
		{
			static::printCommand($cmd);
		}

		$res = exec($cmd, $tmp);
		$output = implode("\n", $tmp);

		return $res;
	}

	public static function fixConfig($destPath, $patchContent)
	{
		if (trim($patchContent) == '')
		{
			return false;
		}

		if (!file_exists($destPath))
		{
			return false;
		}

		$content = file_get_contents($destPath);
		if (mb_strpos($content, $patchContent) === false)
		{
			$content .= "\n" . $patchContent . "\n";
			file_put_contents($destPath, $content);

			return true;
		}

		return false;
	}

	public static function loadFromEnv($path, $name)
	{
		$result = null;

		$content = file_get_contents($path);
		$re = '{' .
				preg_quote($name) . '="(.+?)"'
				. '|' . preg_quote($name) . "='(.+?)'"
				. '|' . preg_quote($name) . "=(.+?)\n"
			. '}si';
		if (!preg_match($re, $content, $m))
		{
			return $result;
		}

		unset($m[0]);
		$m = array_values(array_filter($m));
		if (count($m) == 0)
		{
			return $result;
		}

		$result = trim($m[0]);

		static::updateEnv($name, $result);

		return $result;
	}

	public static function runWinCmd($command, &$output)
	{
		$command = str_replace('&', '^&', $command);
		$cmd = 'cmd.exe /c ' . $command . ' 2>/dev/null';

		static::printCommand($cmd);

		return exec($cmd, $output);
	}

	public static function runCScript($arguments = [])
	{
		if (empty($arguments))
		{
			return;
		}

		$args = '"' . implode('" "', $arguments) . '"';
		$cmd = 'cscript.exe ' . $args;
		$cmd = str_replace('&', '^&', $cmd);

		static::printCommand($cmd);

		ob_start();
		$result = static::run($cmd . ' 2>/dev/null');
		ob_end_clean();

		return $result;
	}

	public static function getWinEnvVariable($name)
	{
		$result = '';

		if (trim($name) == '')
		{
			return $result;
		}

		$result = trim(static::runWinCmd('echo "%' . $name . '%"', $output));

		return $result;
	}

	public static function convertToWinPath($path)
	{
		$path = str_replace([
				'C:\\',
				'\\',
			], [
				'/mnt/c/',
				'/',
			], $path);

		return $path;
	}

	public static function isWSL()
	{
		return (isset($_SERVER['IS_WSL']))
			|| isset($_SERVER['WSL_DISTRO_NAME']);
	}

	public static function isUbuntu()
	{
		static $result = null;
		if ($result !== null)
		{
			return $result;
		}

		if (!static::checkCommand('lsb_release'))
		{
			$result = false;
		}
		else
		{
			static::runGetContent('lsb_release -a' . ' 2>/dev/null', $os, true);

			$result = (mb_stripos($os, 'debian') !== false)
				|| (mb_stripos($os, 'ubuntu') !== false);
		}

		return $result;
	}

	public static function getReplacedEnvVariables($path)
	{
		$result = $path;

		if (str_starts_with($result, '~'))
		{
			$result = $_SERVER['HOME'] . mb_substr($result, 1);
		}

		$result = str_replace([
				'$HOME',
				'$PATH',
			], [
				$_SERVER['HOME'],
				$_SERVER['PATH'],
			], $result);

		return $result;
	}

	public static function getValues($v)
	{
		return array_unique(array_filter(array_map('trim', explode("\n", trim($v)))));
	}

	public static function getDisplayEnvVariable($varName, $multicolumn = false)
	{
		$value = $_SERVER[$varName];

		$result = array_map(
			fn ($v) => static::getReplacedEnvVariables(trim($v)),
			explode("\n", trim($value))
		);

		if ($multicolumn)
		{
			foreach ($result as $i => $v)
			{
				$cols = explode(';', $v);
				foreach ($cols as $colIndex => $colValue)
				{
					if (trim($colValue) == '')
					{
						$cols[$colIndex] = '-';
					}
				}
				$line = implode("\n\t\t", $cols);
				$result[$i] = $line;
			}
		}

		$value = implode("\n\t", $result);
		if (count($result) > 1)
		{
			$value = "\n\t" . $value;
		}

		return $varName . ' -> ' . $value . "\n";
	}

	public static function getDisplayVariants($values)
	{
		$result = [];
		foreach ($values as $k => $v)
		{
			$result[] = $k . "\t" . $v;
		}

		return implode("\n", $result);
	}


	public static function isMingw()
	{
		$msystem = $_SERVER['MSYSTEM'] ?? '';

		return in_array($msystem, ['MINGW64', 'MINGW32', 'MSYS']);
	}

	public static function updateEnv($name, $value)
	{
		$_SERVER[$name] = $value;
		$_ENV[$name] = $value;

		putenv("$name=$value");
	}

	public static function checkCommand($cmd)
	{
		$test = 'which ' . $cmd;

		ob_start();
		$path = trim(system($test));
		ob_end_clean();

		// skip windows executable from WSL
		if (mb_strpos($path, '/mnt/c/') !== false)
		{
			if (static::isWSL())
			{
				return false;
			}
		}

		if ($path == '')
		{
			return false;
		}

		return true;
	}

	public static function tarCreate($dest, $src)
	{
		if (!static::checkCommand('tar'))
		{
			return;
		}

		if (!is_dir($src))
		{
			echo "Folder $src - not exists.\n";
			return;
		}

		if (file_exists($dest))
		{
			unlink($dest);
		}

		echo "Create archive $dest ...\n";
		chdir($src);
		static::run('tar -cf ' . $dest . ' .');
	}

	public static function sudo($cmd, $sudo = '')
	{
		if (static::isUbuntu())
		{
			$sudo = 'sudo ';
		}
		if (($_SERVER['BX_ROOT_USER'] ?? '') == '1')
		{
			$sudo = '';
		}

		return static::run($sudo . $cmd);
	}

	public static function service($action, $name)
	{
		if (static::isWSL() && static::isUbuntu())
		{
			static::sudo("service $name $action");
		}
		else
		{
			static::sudo("systemctl $action $name");
		}
	}
}
