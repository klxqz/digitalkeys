<?php

class shopDigitalkeysPlugin extends shopPlugin {

    public function productSave($params) {
        if (
                !$this->getSettings('status') ||
                wa()->getStorage()->get('shop/digitalkeysplugin/productSaveOff') ||
                !($digital_keys = waRequest::post('digital_keys'))
        ) {
            return false;
        }

        $product = $params['instance'];
        $product_types = $this->getSettings('product_types');
        if (empty($product_types[$product->type_id])) {
            return false;
        }

        $digitalkeys_model = new shopDigitalkeysPluginModel();

        foreach ($digital_keys as $sku_id => $keys_text) {
            if (!empty($product->skus[$sku_id])) {
                $digitalkeys_model->deleteByField('sku_id', $sku_id);
                $keys = explode("\n", $keys_text);
                $keys = array_map('trim', $keys);
                foreach ($keys as $key) {
                    if (!trim($key)) {
                        continue;
                    }
                    $data = array(
                        'sku_id' => $sku_id,
                        'key' => $key
                    );
                    $digitalkeys_model->insert($data);
                }
            }
        }

        if ($this->getSettings('stock')) {
            $this->recountByProduct($product);
        }
    }

    protected function recountByProduct($product) {
        if ($product instanceof shopProduct) {
            $product_id = $product->id;
        } else {
            $product_id = $product;
            $product = new shopProduct($product);
        }

        $product_model = new shopProductModel();
        $sku_model = new shopProductSkusModel();
        $digitalkeys_model = new shopDigitalkeysPluginModel();

        $stocks = array();


        $data = $product_model->getById($product_id);
        $data['skus'] = $sku_model->getDataByProductId($product_id, true);
        foreach ($data['skus'] as $sku_id => $sku) {
            $stocks[$sku_id] = $digitalkeys_model->countByField('sku_id', $sku_id);
        }

        $data['skus'] = $this->prepareSkus($data['skus'], $stocks);
        wa()->getStorage()->set('shop/digitalkeysplugin/productSaveOff', 1);
        $product->save($data, true);
        wa()->getStorage()->set('shop/digitalkeysplugin/productSaveOff', 0);
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

    public function backendProduct($product) {
        if (!$this->getSettings('status')) {
            return false;
        }

        $type_id = $product['type_id'];
        $product_types = $this->getSettings('product_types');

        $view = wa()->getView();
        $show_tab = isset($product_types[$type_id]) && $product_types[$type_id];
        $view->assign('product', $product);
        $enabled_product_types = array();
        foreach ($product_types as $product_type => $on) {
            if ($on) {
                $enabled_product_types[] = $product_type;
            }
        }
        $view->assign('product_types', $enabled_product_types);
        $view->assign('show_tab', $show_tab);
        $html = $view->fetch('plugins/digitalkeys/templates/BackendProduct.html');
        return array('edit_section_li' => $html);
    }

    public function orderActionPay($params) {
        if (!$this->getSettings('status')) {
            return false;
        }

        $order_id = $params['order_id'];
        $log_model = new shopOrderLogModel();
        $order_model = new shopOrderModel();
        $product_model = new shopProductModel();
        $digitalkeys_model = new shopDigitalkeysPluginModel();
        $order = $order_model->getOrder($order_id);
        $product_types = $this->getSettings('product_types');

        $send_keys = array();
        $recount_products = array();
        foreach ($order['items'] as $item) {
            $product = $product_model->getById($item['product_id']);
            $type_id = $product['type_id'];
            $digital_keys = $digitalkeys_model->getByField('sku_id', $item['sku_id'], true);

            if (!empty($product_types[$type_id]) && $digital_keys) {
                if (!in_array($product['id'], $recount_products)) {
                    $recount_products[] = $product['id'];
                }
                for ($i = 0; $i < $item['quantity'] && $i < count($digital_keys); $i++) {
                    $send_keys[] = array(
                        'sku_id' => $item['sku_id'],
                        'name' => $item['name'],
                        'key' => $digital_keys[$i]['key']
                    );

                    $digitalkeys_model->deleteByField(array('sku_id' => $digital_keys[$i]['sku_id'], 'key' => $digital_keys[$i]['key']));

                    $comment = "Отправка цифрового ключа для товара: " .
                            $item['name'] . ($item['sku_code'] ? "(" . $item['sku_code'] . ")" : "") . " - " . $digital_keys[$i]['key'];
                    $data = array(
                        'action_id' => 'comment',
                        'order_id' => $order_id,
                        'before_state_id' => $order['state_id'],
                        'after_state_id' => $order['state_id'],
                        'text' => $comment
                    );
                    $log_model->add($data);
                }
            }
        }
        if ($send_keys) {
            if ($this->getSettings('stock')) {
                foreach ($recount_products as $recount_product_id) {
                    $this->recountByProduct($recount_product_id);
                }
            }

            $email = !empty($order['contact']['email']) ? $order['contact']['email'] : null;
            if ($email) {
                $general = wa('shop')->getConfig()->getGeneralSettings();
                $template_path = wa()->getDataPath('plugins/digitalkeys/templates/printform/SendDigitalKey.html', false, 'shop', true);
                if (!file_exists($template_path)) {
                    $template_path = wa()->getAppPath('plugins/digitalkeys/templates/printform/SendDigitalKey.html', 'shop');
                }
                $view = wa()->getView();
                $view->assign('send_keys', $send_keys);
                $notification = $view->fetch($template_path);
                $message = new waMailMessage('Заказ ' . shopHelper::encodeOrderId($order_id), $notification);
                $message->setTo($email);
                $from = $general['email'];
                $message->setFrom($from, $general['name']);
                $message->send();
            }
        }
    }

}
