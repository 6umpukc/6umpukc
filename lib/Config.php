<?php

namespace Rodzeta\Siteoptions;

use Rodzeta\Siteoptions\Shell;

final class Config
{
	public static function removeEmptyLines($originalContent)
	{
		$originalContent = preg_replace(
			"{\n\s+\n}si",
			"\n",
			$originalContent
		);

		$originalContent = preg_replace(
			"{\n{2,}}si",
			"\n\n",
			$originalContent
		);

		return $originalContent;
	}

	public static function getTemplate($path)
	{
		$result = '';
		$path = Shell::getRealBinPath() . '/.template/' . $path;
		if (file_exists($path))
		{
			$result = file_get_contents($path);
		}

		return $result;
	}

	public static function getRandomPassword()
	{
		$result = '';

		$length = 17;
		$characters = '0123456789'
			. 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = mb_strlen($characters);
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $characters[random_int(0, $charactersLength - 1)];
		}

		$length = 3;
		$characters = '!_-.,|';
		$charactersLength = mb_strlen($characters);
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $characters[random_int(0, $charactersLength - 1)];
		}

		return $result;
	}

	public static function getRandomName()
	{
		$length = 9;
		$characters = '0123456789'
			. 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = mb_strlen($characters);
		$result = 'usr';
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $characters[random_int(0, $charactersLength - 1)];
		}

		return $result;
	}

	public static function getPublicPrefix()
	{
		return $_SERVER['DIR_PUBLIC'] ?? '';
	}

	public static function getBlock($name, $content)
	{
		$begin = '#bx-' . $name . '-begin';
		$end = '#bx-' . $name . '-end';

		$re = '{' . preg_quote($begin) . '\s.+?' . preg_quote($end) . '}s';

		$wrappedContent = $begin . $content . $end;

		return [
			$re,
			$wrappedContent,
		];
	}

	public static function replaceBlock($name, $content, $originalContent, $beforeTag = '</VirtualHost>')
	{
		list($re, $wrappedContent) = static::getBlock($name, $content);

		if (preg_match($re, $originalContent))
		{
			// replace
			$originalContent = preg_replace($re, $wrappedContent, $originalContent);
		}
		else
		{
			// add
			$originalContent = str_replace(
				$beforeTag,
				"\n" . $wrappedContent . "\n\n" . $beforeTag,
				$originalContent
			);
		}

		// remove empty block
		if (trim($content) == '')
		{
			$originalContent = str_replace($wrappedContent, '', $originalContent);
		}

		return $originalContent;
	}

	public static function replaceContent($linesFrom, $linesTo, $originalContent, &$count = null)
	{
		$re = '{' . implode('\s*', array_map('preg_quote', $linesFrom)) . '}si';

		$originalContent = preg_replace($re, implode("\n", $linesTo), $originalContent, -1, $count);

		return $originalContent;
	}
}