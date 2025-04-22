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
        add_meta_box('pd_meta', 'Leads & Config', [__CLASS__, 'render_meta_box'], 'product', 'normal', 'high');
    }

    public static function render_meta_box($post) {
        echo '<h4>📋 Leads associés</h4>';
        $leads = json_decode(get_post_meta($post->ID, '_leads_liste', true), true) ?: [];
        if ($leads) {
            foreach ($leads as $i => $l) {
                echo '<div style="border:1px solid #ccc;padding:8px;margin-bottom:8px;">';
                foreach (['nom'=>'Nom','telephone'=>'Téléphone','email'=>'Email','departement'=>'Département','facture'=>'Facture','statut'=>'Statut','achete'=>'Acheté'] as $k=>$label) {
                    $val = ($k==='achete') ? (!empty($l[$k])?'✅ Oui':'❌ Non') : esc_html($l[$k] ?? '');
                    echo "<p><strong>{$label}:</strong> {$val}</p>";
                }
                echo "<p><label><input type='checkbox' name='pd_delete_lead[]' value='{$i}'> Supprimer</label></p>";
                echo '</div>';
            }
        } else {
            echo '<p>Aucun lead.</p>';
        }
        echo '<h4>⏱ Paliers de prix</h4><table><tr><th>Délai</th><th>Prix</th><th>Suppr</th></tr>';
        $tiers = [];
        foreach (get_post_meta($post->ID) as $k => $v) {
            if (strpos($k, '_delai_palier_') === 0) {
                $idx = intval(str_replace('_delai_palier_', '', $k));
                $tiers[$idx] = intval($v[0]);
            }
        }
        ksort($tiers);
        foreach ($tiers as $i => $d) {
            $p = get_post_meta($post->ID, '_prix_palier_' . $i, true);
            echo "<tr>
                <td><input type='number' name='pd_delai[{$i}]' value='{$d}' style='width:60px;'></td>
                <td><input type='number' step='0.01' name='pd_prix[{$i}]' value='{$p}' style='width:60px;'></td>
                <td><input type='checkbox' name='pd_delete_tier[]' value='{$i}'></td>
            </tr>";
        }
        echo '</table>';
        $a = get_post_meta($post->ID, '_qualite_aplus', true);
        $amin = get_post_meta($post->ID, '_qualite_A_min', true);
        $amax = get_post_meta($post->ID, '_qualite_A_max', true);
        $bmax = get_post_meta($post->ID, '_qualite_B_max', true);
        echo "<h4>⚡ Seuils qualité</h4>
        <p>A+ ≤ <input type='number' name='pd_a' value='{$a}' style='width:60px;'></p>
        <p>A entre <input type='number' name='pd_amin' value='{$amin}' style='width:60px;'> et <input type='number' name='pd_amax' value='{$amax}' style='width:60px;'></p>
        <p>B ≤ <input type='number' name='pd_b' value='{$bmax}' style='width:60px;'></p>";
    }

    public static function save_meta($post_id) {
        if (!isset($_POST['pd_meta_nonce']) || !wp_verify_nonce($_POST['pd_meta_nonce'], 'pd_meta_save')) {
            return;
        }
        if (!empty($_POST['pd_delete_lead'])) {
            $leads = json_decode(get_post_meta($post_id, '_leads_liste', true), true) ?: [];
            foreach ($_POST['pd_delete_lead'] as $i) {
                unset($leads[intval($i)]);
            }
            update_post_meta($post_id, '_leads_liste', wp_json_encode(array_values($leads), JSON_UNESCAPED_UNICODE));
        }
        if (!empty($_POST['pd_delete_tier'])) {
            foreach ($_POST['pd_delete_tier'] as $i) {
                delete_post_meta($post_id, '_delai_palier_' . intval($i));
                delete_post_meta($post_id, '_prix_palier_' . intval($i));
            }
        }
        if (!empty($_POST['pd_delai'])) {
            foreach ($_POST['pd_delai'] as $i => $d) {
                update_post_meta($post_id, '_delai_palier_' . intval($i), intval($d));
            }
        }
        if (!empty($_POST['pd_prix'])) {
            foreach ($_POST['pd_prix'] as $i => $p) {
                update_post_meta($post_id, '_prix_palier_' . intval($i), floatval($p));
            }
        }
        if (isset($_POST['pd_a'])) update_post_meta($post_id, '_qualite_aplus', intval($_POST['pd_a']));
        if (isset($_POST['pd_amin'])) update_post_meta($post_id, '_qualite_A_min', intval($_POST['pd_amin']));
        if (isset($_POST['pd_amax'])) update_post_meta($post_id, '_qualite_A_max', intval($_POST['pd_amax']));
        if (isset($_POST['pd_b'])) update_post_meta($post_id, '_qualite_B_max', intval($_POST['pd_b']));
    }
}
