<?php
namespace PD\Modules;

class DynamicPricing {
    public static function init() {
        add_filter('woocommerce_product_get_price', [__CLASS__, 'apply_dynamic_price'], 10, 2);
        add_filter('woocommerce_product_get_regular_price', [__CLASS__, 'apply_dynamic_price'], 10, 2);
        add_filter('woocommerce_variantion_get_price', [__CLASS__, 'apply_dynamic_price'], 10, 2);
        add_filter('woocommerce_get_price_html', [__CLASS__, 'filter_price_html'], 10, 2);
    }

    public static function apply_dynamic_price($price, $product) {
        $pid = $product->get_id();
        if (! metadata_exists('post', $pid, '_lead_reference')) {
            return $price;
        }
        $elapsed = max(0, round((current_time('timestamp') - get_post_time('U', false, $pid)) / 60));
        $tiers = [];
        foreach (get_post_meta($pid) as $key => $vals) {
            if (strpos($key, '_delai_palier_') === 0) {
                $i = intval(str_replace('_delai_palier_', '', $key));
                $tiers[$i] = [
                    'delai' => intval($vals[0]),
                    'prix' => floatval(get_post_meta($pid, '_prix_palier_' . $i, true)),
                ];
            }
        }
        if (empty($tiers)) {
            return $price;
        }
        ksort($tiers);
        $current = $price;
        foreach ($tiers as $tier) {
            if ($elapsed >= $tier['delai']) {
                $current = $tier['prix'];
            } else {
                break;
            }
        }
        return $current;
    }

    public static function filter_price_html($html, $product) {
        if (metadata_exists('post', $product->get_id(), '_lead_reference')) {
            return wc_price($product->get_price());
        }
        return $html;
    }
}
