<?php

class shopDigitalkeysPlugin extends shopPlugin {

    public function backendProduct($product) {
        $type_id = $product['type_id'];
        $product_types = $this->getSettings('product_types');
        if ($this->getSettings('status') && isset($product_types[$type_id]) && $product_types[$type_id]) {
            $view = wa()->getView();
            $view->assign('product', $product);
            $html = $view->fetch('plugins/digitalkeys/templates/BackendProduct.html');
            return array('edit_section_li' => $html);
        }
    }

    public function orderActionPay($params) {
        $order_model = new shopOrderModel();
        $product_model = new shopProductModel();
        $digital_keys_model = new shopDigitalkeysPluginModel();
        $order = $order_model->getOrder($params['order_id']);
        
        $product_types = $this->getSettings('product_types');
        
        foreach ($order['items'] as $item) {
            $product = $product_model->getById($item['product_id']);
            $type_id = $product['type_id'];
            $digital_keys = $digital_keys_model->getByField('sku_id', $item['sku_id'], true);
      
            if(isset($product_types[$type_id]) && $product_types[$type_id] && $digital_keys) {
                print_r($digital_keys);
            }
            //if()
            //
            //print_r($digital_keys);
        }
        exit('dsds');
        
    }

}
