<?php

try {
    $files = array(
        'plugins/digitalkeys/js/digitalkeys.js',
        'plugins/digitalkeys/lib/actions/shopDigitalkeysPluginBackendSave.controller.php',
        'plugins/digitalkeys/lib/config/uninstall.php',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}