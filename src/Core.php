<?php
namespace PD;

class Core {
    public static function init() {
        $modules = [
            Modules\DynamicPricing::class,
            Modules\QualityScore::class,
            Modules\FrontUI::class,
            Modules\AdminUI::class,
            Modules\WebhookTally::class,
        ];
        foreach ($modules as $module) {
            if (method_exists($module, 'init')) {
                call_user_func([$module, 'init']);
            }
        }
    }
}
