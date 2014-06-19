<?php

class shopDigitalkeysPluginBackendSaveController extends waJsonController {

    public function execute() {

        $post = waRequest::post('product', array());

        $product = isset($post['id']) ? new shopProduct($post['id']) : null;
        if (!$product) {
            throw new waException(_w("Unknown product"));
        }

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
    }

}
