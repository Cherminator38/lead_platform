<?php
namespace PD;

class Core {
    public static function init() {
        $modules = [
            Modules\DynamicPricing::class,
            Modules\AdminUI::class,
            Modules\FrontUI::class,
            Modules\WebhookTally::class,
            Modules\QualityScore::class,
        ];
        foreach ($modules as $module) {
            if (method_exists($module, 'init')) {
                call_user_func([$module, 'init']);
            }
        }
    }
}
