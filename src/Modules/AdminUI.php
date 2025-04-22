<?php
namespace PD\Modules;

use PD\Modules\QualityScore;

class AdminUI {
    public static function init() {
        add_action('edit_form_after_title', [__CLASS__, 'preview_lead']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_product', [__CLASS__, 'save_meta'], 10, 1);
    }

    public static function preview_lead($post) {
        if ($post->post_type !== 'product') return;
        $ref = get_post_meta($post->ID, '_lead_reference', true);
        $leads = json_decode(get_post_meta($post->ID, '_leads_liste', true), true) ?: [];
        $lead = $leads[0] ?? [];
        $score = QualityScore::get_score($post->ID);
        echo '<div style="background:#eef;padding:10px;margin-bottom:15px;border-left:4px solid #0073aa;">';
        echo '<h3>Aperçu Lead</h3>';
        echo '<p><strong>Réf. lead :</strong> ' . esc_html($ref) . '</p>';
        echo '<p><strong>Département :</strong> ' . esc_html($lead['departement'] ?? '') . '</p>';
        echo '<p><strong>Facture :</strong> ' . esc_html($lead['facture'] ?? '') . '</p>';
        echo '<p><strong>Statut :</strong> ' . esc_html($lead['statut'] ?? '') . '</p>';
        echo '<p><strong>Score qualité :</strong> ' . esc_html($score) . '</p>';
        echo '</div>';
    }

    public static function add_meta_boxes() {
        add_meta_box('pd_config', 'Paliers & Seuils', [__CLASS__, 'render_meta_box'], 'product', 'normal', 'high');
    }

    public static function render_meta_box($post) {
        echo '<p>Configuration des paliers de prix et seuils qualité ici.</p>';
        // Inside here, you'd replicate the meta-box code from AdminUI
    }

    public static function save_meta($post_id) {
        // Handle saving meta from the meta box
    }
}
