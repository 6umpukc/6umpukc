<?php

namespace Rodzeta\Siteoptions;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

final class Encoding
{
	const DEFAULT_EXTENSIONS = [
		'php',
		'xml',
		'json',
		'js',
		'css',
	];

	public static function getWithoutComments($content)
	{
		$result = '';

		foreach (token_get_all($content) as $token)
		{
			if (is_string($token))
			{
				$result .= $token;
				continue;
			}

			list($id, $text) = $token;
			switch ($id)
			{
				case T_COMMENT:
				case T_DOC_COMMENT:
					// skip comment
					break;

				default:
					$result .= $text;
					break;
			}
		}

		return $result;
	}

	public static function isUtf($content)
	{
		return mb_detect_encoding($content, 'UTF-8', true);
	}

	public static function getList($path, $additionalExclude = [], $onlyExtensions = null)
	{
		if ($onlyExtensions === null)
		{
			$onlyExtensions = static::DEFAULT_EXTENSIONS;
		}
		$onlyExtensions = array_flip($onlyExtensions);

		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$path,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($it as $f)
		{
			$name = $f->getPathname();
			if ((strpos($name, '.git') !== false)
					|| (strpos($name, '.vscode') !== false)
					|| (strpos($name, '.idea') !== false)
					|| (strpos($name, '.dev') !== false)
					|| (strpos($name, 'vendor') !== false))
			{
				continue;
			}

			foreach ($additionalExclude as $checkPath)
			{
				if (strpos($name, $checkPath) !== false)
				{
					continue 2;
				}
			}

			if ($f->isDir())
			{
				continue;
			}

			if (!$f->isFile())
			{
				continue;
			}

			$ext = $f->getExtension();
			if (!isset($onlyExtensions[$ext]))
			{
				continue;
			}

			yield $f;
		}
	}
}