<?php

namespace WPStaging\Pro\Template;

use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Settings\Settings;
use WPStaging\Framework\TemplateEngine\TemplateEngine;

class TemplateServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->singleton(TemplateEngine::class);
        $this->container->singleton(ProTemplateIncluder::class);
    }

    protected function addHooks()
    {
        add_action(TemplateEngine::ACTION_AFTER_EXISTING_CLONES, $this->container->callback(ProTemplateIncluder::class, 'addEditCloneLink'), 10, 3);
        add_action(TemplateEngine::ACTION_AFTER_EXISTING_CLONES, $this->container->callback(ProTemplateIncluder::class, 'addPushButton'), 10, 3);
        add_action(TemplateEngine::ACTION_AFTER_EXISTING_CLONES, $this->container->callback(ProTemplateIncluder::class, 'addGenerateLoginLink'), 10, 3);
        add_action(TemplateEngine::ACTION_AFTER_EXISTING_CLONES, $this->container->callback(ProTemplateIncluder::class, 'addSyncAccountButton'), 10, 2);
        add_action(TemplateEngine::ACTION_MULTI_SITE_CLONE_OPTION, $this->container->callback(ProTemplateIncluder::class, 'addMultiSiteCloneOption'), 10, 3);
        add_action(TemplateEngine::ACTION_BACKUP_TAB, $this->container->callback(ProTemplateIncluder::class, 'addBackupOption'), 10, 3);
        add_action(Settings::ACTION_WPSTG_PRO_SETTINGS, $this->container->callback(ProTemplateIncluder::class, 'addProSettings'), 10);
        add_action(TemplateEngine::HOOK_RENDER_PRO_TEMPLATES, $this->container->callback(ProTemplateIncluder::class, 'addStagingModalTemplates'), 10);

        Hooks::registerInternalHook(Settings::ACTION_WPSTG_PRO_SETTINGS, $this->container->callback(ProTemplateIncluder::class, 'addProSettings'));
    }
}
