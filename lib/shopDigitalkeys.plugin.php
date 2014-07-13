<?php

class shopDigitalkeysPlugin extends shopPlugin {

    public function backendProduct($product) {
        $type_id = $product['type_id'];
        $product_types = $this->getSettings('product_types');
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $show_tab =  isset($product_types[$type_id]) && $product_types[$type_id];
            $view->assign('product', $product);
            $enabled_product_types = array();
            foreach($product_types as $product_type => $on) {
                if($on) {
                    $enabled_product_types[] = $product_type;
                }
            }
            $view->assign('product_types', $enabled_product_types);
            $view->assign('show_tab', $show_tab);
            $html = $view->fetch('plugins/digitalkeys/templates/BackendProduct.html');
            return array('edit_section_li' => $html);
        }
    }

    public function orderActionPay($params) {
        $order_id = $params['order_id'];
        $log_model = new shopOrderLogModel();
        $order_model = new shopOrderModel();
        $product_model = new shopProductModel();
        $digital_keys_model = new shopDigitalkeysPluginModel();
        $order = $order_model->getOrder($order_id);
        $product_types = $this->getSettings('product_types');

        $send_keys = array();

        foreach ($order['items'] as $item) {
            $product = $product_model->getById($item['product_id']);
            $type_id = $product['type_id'];
            $digital_keys = $digital_keys_model->getByField('sku_id', $item['sku_id'], true);

            if (isset($product_types[$type_id]) && $product_types[$type_id] && $digital_keys) {
                for ($i = 0; $i < $item['quantity'] && $i < count($digital_keys); $i++) {
                    $send_keys[] = array(
                        'sku_id' => $item['sku_id'],
                        'name' => $item['name'],
                        'key' => $digital_keys[$i]['key']
                    );
                    $sql = "DELETE FROM `shop_digital_keys` WHERE `sku_id` = '" . (int) $digital_keys[$i]['sku_id']
                            . "' AND `key` = '" . $digital_keys_model->escape($digital_keys[$i]['key']) . "'";
                    $digital_keys_model->query($sql);
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

        $email = isset($order['contact']['email']) ? $order['contact']['email'] : null;
        if ($email && $send_keys) {
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
