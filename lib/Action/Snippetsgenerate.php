<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Snippetsgenerate extends Base
{
	public function getName()
	{
		return 'snippets-generate';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName()
			. ' - Сгенерировать снипеты из указанных markdown-файлов для WindowsTerminal, VS Code и т.д.' . "\n"
			. Shell::getDisplayEnvVariable('BX_SNIPPETS_SRC');
	}

	protected function convertToBitrixPhpConsoleSnippet($command)
	{
		$result = [
			'name' => $command['title'],
			'command' => [
				//'action' => 'sendInput',
				'input' => $command['command'],
			],
		];

		return $result;
	}

	protected function convertToWindowsTerminalSnippet($command)
	{
		$result = $this->convertToBitrixPhpConsoleSnippet($command);
		$result['command']['action'] = 'sendInput';

		if ($command['type'] != 'bash')
		{
			return [];
		}

		return $result;
	}

	protected function parseCommandFromChunk($command)
	{
		$type = 'bash';

		if (preg_match('{(.+?)```(.+?)```}si', $command, $matches))
		{
			$code = $matches[2];
			if (str_starts_with($code, 'php'))
			{
				$type = 'php';
				$code = substr($code, 4);
			}

			return [
				'title' => trim($matches[1]),
				'command' => trim($code) . "\n",
				'type' => $type,
			];
		}

		if (preg_match('{(.+?)`(.+?)`}si', $command, $matches))
		{
			return [
				'title' => trim($matches[1]),
				'command' => trim($matches[2]),
				'type' => $type,
			];
		}

		return [];
	}

	protected function parseCommandsFromFile($file)
	{
		$content = file_get_contents($file);

		$currentGroup = '';
		foreach (explode('## ', $content) as $command)
		{
			$command = trim($command);

			// check group by header
			if (str_starts_with($command, '# '))
			{
				$currentGroup = trim($command, '# ');
				continue;
			}

			$commandWithHeader = explode('# ', $command);
			if (!empty($commandWithHeader[1]))
			{
				$command = $commandWithHeader[0];
			}

			$commandWithTitle = $this->parseCommandFromChunk($command);
			if (empty($commandWithTitle))
			{
				continue;
			}

			$commandWithTitle['group'] = $currentGroup;

			yield $commandWithTitle;

			if (!empty($commandWithHeader[1]))
			{
				$currentGroup = $commandWithHeader[1];
			}
		}
	}

	public function run()
	{
		$files = Shell::getValues($_SERVER['BX_SNIPPETS_SRC'] ?? '');

		$wtSnippets = [];
		$bitrixPhpConsoleSnippets = [];

		foreach ($files as $f)
		{
			$f = Shell::getReplacedEnvVariables($f);
			if (!file_exists($f))
			{
				continue;
			}

			foreach ($this->parseCommandsFromFile($f) as $command)
			{
				// collect snippets for Windows Terminal
				$wtCommand = $this->convertToWindowsTerminalSnippet($command);
				if (!empty($wtCommand))
				{
					$wtSnippets[] = $wtCommand;
				}

				// collect php console snippets
				if (!str_starts_with($command['command'], 'bx '))
				{
					$bitrixPhpConsoleCommand = $this->convertToBitrixPhpConsoleSnippet($command);
					if (!empty($bitrixPhpConsoleCommand))
					{
						$bitrixPhpConsoleSnippets[] = $bitrixPhpConsoleCommand;
					}
				}
			}
		}

		file_put_contents(
			$this->getDevPath('windows_terminal_snippets.json'),
			json_encode($wtSnippets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
		);

		file_put_contents(
			$this->getDevPath('bitrix_php_console_snippets.json'),
			json_encode($bitrixPhpConsoleSnippets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
		);

		echo 'Snippets generated to ' . $this->getDevPath() . "\n";
	}
}
