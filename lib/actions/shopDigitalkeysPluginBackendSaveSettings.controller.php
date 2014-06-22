<?php

class shopDigitalkeysPluginBackendSavesettingsController extends waJsonController {

    protected $plugin_id = array('shop', 'digitalkeys');
    protected $tpl_path = 'plugins/digitalkeys/templates/printform/SendDigitalKey.html';

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $settings = waRequest::post('shop_digitalkeys');

            foreach ($settings as $name => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $app_settings_model->set($this->plugin_id, $name, $value);
            }

            if (waRequest::post('reset_tpl')) {
                $template_path = wa()->getDataPath($this->tpl_path, false, 'shop', true);
                @unlink($template_path);
            } else {

                $post_template = waRequest::post('template_path');

                $template_path = wa()->getDataPath($this->tpl_path, false, 'shop', true);
                if (!file_exists($template_path)) {
                    $template_path = wa()->getAppPath($this->tpl_path, 'shop');
                }

                $template_content = file_get_contents($template_path);
                if ($template_content != $post_template) {
                    $template_path = wa()->getDataPath($this->tpl_path, false, 'shop', true);

                    $f = fopen($template_path, 'w');
                    if (!$f) {
                        throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
                    }
                    fwrite($f, $post_template);
                    fclose($f);
                }
            }

            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
