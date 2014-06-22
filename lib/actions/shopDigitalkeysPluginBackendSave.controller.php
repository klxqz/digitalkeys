<?php

class shopDigitalkeysPluginBackendSaveController extends waJsonController {

    protected $plugin_id = array('shop', 'digitalkeys');

    public function execute() {

        $post = waRequest::post('product', array());
        $product = isset($post['id']) ? new shopProduct($post['id']) : null;
        if (!$product) {
            throw new waException(_w("Unknown product"));
        }

        $app_settings_model = new waAppSettingsModel();
        $model_sku = new shopProductSkusModel();
        $product_model = new shopProductModel();

        $product_types = $app_settings_model->get($this->plugin_id, 'product_types');

        $digital_keys = waRequest::post('digital_keys', array());

        if (isset($product_types[$product->type_id]) && $product_types[$product->type_id] && $digital_keys) {
            $model = new shopDigitalkeysPluginModel();

            $skus = array_keys($product->skus);
            $model->deleteByField('sku_id', $skus);

            foreach ($digital_keys as $sku_id => $keys) {
                foreach ($keys as $key) {
                    if (!trim($key)) {
                        continue;
                    }
                    $data = array(
                        'sku_id' => $sku_id,
                        'key' => $key
                    );
                    $model->insert($data);
                }
            }

            $stocks = array();
            foreach ($product->skus as $sku_id => $sku) {
                $stocks[$sku_id] = $model->countByField('sku_id', $sku_id);
            }

            if ($app_settings_model->get($this->plugin_id, 'stock')) {
                $data = $product_model->getById($product->id);
                $data['skus'] = $model_sku->getDataByProductId($product->id, true);
                $data['skus'] = $this->prepareSkus($data['skus'], $stocks);
                $product->save($data, true);
            }
        }

        $this->response['id'] = $product->id;
        $this->response['name'] = $product->name;
        $this->response['raw'] = $this->workupData($product->getData());

        $runout = $product->getRunout(0);
        if (!empty($runout['product'])) {
            $runout['product']['date_str'] = wa_date("humandate", $runout['product']['date']);
            $runout['product']['days_str'] = _w('%d day', '%d days', $runout['product']['days']);
            if ($runout['product']['days'] < 3 * 365 && $runout['product']['days'] > 0) {
                $runout['product_str'] = sprintf(_w('Based on last 30 days sales dynamic (%d items of %s sold during last 30 days), you will run out of %s in <strong>%d days</strong> (on %s)'), $sales_rate * 30, $product->name, $product->name, $runout['product']['days'], wa_date("humandate", $runout['product']['date'])
                );
            }
        } else {
            $runout['product'] = new stdClass(); /* {} */
        }
        if (!empty($runout['sku'])) {
            foreach ($runout['sku'] as &$sk_r) {
                if (empty($sk_r['stock'])) {
                    $sk_r['date_str'] = wa_date("humandate", $sk_r['date']);
                    $sk_r['days_str'] = _w('%d day', '%d days', $sk_r['days']);
                } else {
                    foreach ($sk_r['stock'] as &$st_r) {
                        $st_r['date_str'] = wa_date("humandate", $st_r['date']);
                        $st_r['days_str'] = _w('%d day', '%d days', $st_r['days']);
                    }
                }
            }
            unset($sk_r, $st_r);
        } else {
            $runout['sku'] = new stdClass(); /* {} */
        }
        $this->response['raw']['runout'] = $runout;


        $this->response['storefront_map'] = $product_model->getStorefrontMap($product->id);
    }

    protected function prepareSkus($skus, $stocks) {
        foreach ($skus as $sku_id => &$sku) {
            if (!$sku['stock']) {
                $sku['stock'][0] = $stocks[$sku_id];
            } else {
                foreach ($sku['stock'] as &$stock) {
                    $stock = 0;
                }
                $keys = array_keys($sku['stock']);
                $first_key = array_shift($keys);
                $sku['stock'][$first_key] = $stocks[$sku_id];
            }
        }
        return $skus;
    }

    public function workupData($data) {
        $currency = $data['currency'] ? $data['currency'] : $this->getConfig()->getCurrency();

        $file_names = array();  // sku_id => filename of attachment

        foreach ($data['skus'] as &$sku) {
            if (!isset($sku['file_name'])) {
                $file_names[$sku['id']] = '';   // need to obtain filename
            }
            $sku['price_str'] = wa_currency($sku['price'], $currency);
            $sku['stock_icon'] = array();
            $sku['stock_icon'][0] = shopHelper::getStockCountIcon($sku['count']);
            if (!empty($sku['stock'])) {
                foreach ($sku['stock'] as $stock_id => $count) {
                    $sku['stock_icon'][$stock_id] = shopHelper::getStockCountIcon($count, $stock_id);
                }
            }
        }
        unset($sku);

        // obtain filename
        if ($file_names) {
            $product_skus_model = new shopProductSkusModel();
            $file_names = $product_skus_model->select('id, file_name')->where("id IN('" . implode("','", array_keys($file_names)) . "')")->fetchAll('id', true);
            foreach ($file_names as $sku_id => $file_name) {
                $data['skus'][$sku_id]['file_name'] = $file_name;
            }
        }

        return $data;
    }

}
