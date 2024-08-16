<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Help extends Base
{
	private $commands;
	private $action;

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [command] - Выводит информацию по поддерживаемым командам';
	}

	protected function printCommandList()
	{
		echo $this->getDescription() . "\n";

		foreach ($this->commands as $name => $command)
		{
			$commandDescription = 'bx ' . mb_strtolower($name);
			echo $commandDescription . "\n";
		}
	}

	protected function printCommand()
	{
		$command = !empty($this->commands[$this->action])? $this->commands[$this->action] : [];

		if (empty($command))
		{
			$this->printCommandList();
			return;
		}

		if (!empty($command['descr']))
		{
			$commandDescription = $command['descr'];
		}
		else if (!empty($command['class']))
		{
			$commandDescription = $this->create($command['class'])->getDescription();
		}

		if ($commandDescription != '')
		{
			echo $commandDescription . "\n";
		}
	}


	public function run()
	{
		$this->action = $this->params[0] ?? '';
		$this->commands = $this->getCommands();

		if (empty($this->action))
		{
			$this->printCommandList();
		}
		else
		{
			$this->printCommand();
		}

		echo "\n";
	}
}

