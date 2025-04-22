<?php
namespace PD\Modules;

use PD\Modules\QualityScore;

class FrontUI {
    public static function init() {
        add_action('woocommerce_single_product_summary', [__CLASS__, 'display_lead_info'], 15);
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }

    public static function display_lead_info() {
        global $post;
        if ($post->post_type !== 'product' || ! metadata_exists('post', $post->ID, '_lead_reference')) {
            return;
        }
        $ref = get_post_meta($post->ID, '_lead_reference', true);
        $leads = (array) json_decode(get_post_meta($post->ID, '_leads_liste', true), true);
        $lead = $leads[0] ?? [];
        echo '<div class="pd-lead-info" style="margin:15px 0;padding:10px;border:1px solid #ccc;">';
        echo '<p><strong>Réf. lead :</strong> ' . esc_html($ref) . '</p>';
        if (!empty($lead['departement'])) {
            echo '<p><strong>Département :</strong> ' . esc_html($lead['departement']) . '</p>';
        }
        $score = QualityScore::get_score($post->ID);
        echo '<p><strong>Score qualité :</strong> ' . esc_html($score) . '</p>';
        echo '</div>';
    }
}
