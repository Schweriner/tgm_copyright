<?php

declare(strict_types=1);

namespace TGM\TgmCopyright\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('tgmCopyright_pluginCtypeUpgradeWizard')]
class TgmCopyrightMainMigration extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return ['tgmcopyright_main' => 'tgmcopyright_main'];
    }

    public function getTitle(): string
    {
        return 'Migrate "tgmcopyright_main" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "tgmcopyright_main" plugin is now registered as content element. Update migrates existing plugins to content elements.';
    }
}
