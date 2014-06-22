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

        $digital_keys = waRequest::post('digital_keys', array());
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

}
