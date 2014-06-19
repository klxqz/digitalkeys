<?php

class shopDigitalkeysPluginBackendAction extends waViewAction {

    public function execute() {
        $id = waRequest::get('id', null, waRequest::TYPE_INT);

        $product = new shopProduct($id);
        if (!$product) {
            throw new waException(_w("Unknown product"));
        }
        $model = new shopDigitalkeysPluginModel();
        $skus = array_keys($product->skus);
        $digital_keys = array();
        foreach ($skus as $sku) {
            $digital_keys[$sku] = $model->getByField('sku_id', $sku, true);
        }
        $this->view->assign('product', $product);
        $this->view->assign('digital_keys', $digital_keys);
    }

}
