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
			. Shell::getDisplayEnvVariable('BX_BACKUP_GIT_REPOS');
	}
}
