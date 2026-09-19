<?php

declare(strict_types=1);

namespace UpCore;

use UpCore\Admin\Menu;
use UpCore\Modules\ApplicationPasswords\ApplicationPasswordsModule;
use UpCore\Modules\Captcha\CaptchaModule;
use UpCore\Modules\Comments\CommentsModule;
use UpCore\Modules\CoreCleanup\CoreCleanupModule;
use UpCore\Modules\Cron\CronModule;
use UpCore\Modules\FrontendAssets\FrontendAssetsModule;
use UpCore\Modules\Hardening\HardeningModule;
use UpCore\Modules\Heartbeat\HeartbeatModule;
use UpCore\Modules\LoginThrottle\LoginThrottleModule;
use UpCore\Modules\LoginUrl\LoginUrlModule;
use UpCore\Modules\Revisions\RevisionsModule;
use UpCore\Modules\UserEnumeration\UserEnumerationModule;
use UpCore\Modules\XmlRpc\XmlRpcModule;
use UpCore\Rest\ModulesController;

final class Plugin
{
    private ModuleManager $modules;

    private Settings $settings;

    public function __construct()
    {
        $this->settings = new Settings();
        $this->modules = new ModuleManager();

        foreach ($this->default_modules() as $module) {
            $this->modules->add($module);
        }
    }

    /** @return Module[] */
    private function default_modules(): array
    {
        return [
            new CommentsModule(),
            new XmlRpcModule(),
            new CaptchaModule(),
            new LoginUrlModule(),
            new LoginThrottleModule(),
            new HardeningModule(),
            new ApplicationPasswordsModule(),
            new UserEnumerationModule(),
            new CoreCleanupModule(),
            new HeartbeatModule(),
            new RevisionsModule(),
            new FrontendAssetsModule(),
            new CronModule(),
        ];
    }

    public function boot(): void
    {
        $this->modules->boot($this->settings);

        add_action('rest_api_init', function (): void {
            (new ModulesController($this->modules, $this->settings))->register_routes();
        });

        add_action('admin_menu', function (): void {
            (new Menu($this->modules, $this->settings))->register();
        });
    }
}
