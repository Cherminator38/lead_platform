<?php
namespace PD\Modules;

class QualityScore {
    public static function init() {
        // Pas d'init requis
    }

    public static function get_score(int $post_id): string {
        $elapsed = max(0, round((current_time('timestamp') - get_post_time('U', false, $post_id)) / 60));
        $seuil_aplus = intval(get_post_meta($post_id, '_qualite_aplus', true) ?: 5);
        $seuil_Amin  = intval(get_post_meta($post_id, '_qualite_A_min', true) ?: 1440);
        $seuil_Amax  = intval(get_post_meta($post_id, '_qualite_A_max', true) ?: 2880);
        $seuil_Bmax  = intval(get_post_meta($post_id, '_qualite_B_max', true) ?: 4320);

        if ($elapsed <= $seuil_aplus) {
            return 'A+';
        }
        if ($elapsed >= $seuil_Amin && $elapsed <= $seuil_Amax) {
            return 'A';
        }
        if ($elapsed <= $seuil_Bmax) {
            return 'B';
        }
        return 'C';
    }
}
