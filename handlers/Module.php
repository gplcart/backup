<?php

/**
 * @package Backup
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\backup\handlers;

use gplcart\core\helpers\Zip as ZipHelper;
use gplcart\core\models\Language as LanguageModel;

/**
 * Provides methods to backup modules
 */
class Module
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Zip helper class instance
     * @var \gplcart\core\helpers\Zip $zip
     */
    protected $zip;

    /**
     * @param LanguageModel $language
     * @param ZipHelper $zip
     */
    public function __construct(LanguageModel $language, ZipHelper $zip)
    {
        $this->zip = $zip;
        $this->language = $language;
    }

    /**
     * Creates a module backup
     * @param array $data
     * @param \gplcart\modules\backup\models\Backup $model
     * @return boolean
     */
    public function backup(array $data, $model)
    {
        $directory = GC_PRIVATE_MODULE_DIR . '/backup';
        if (!file_exists($directory) && !mkdir($directory, 0775, true)) {
            return false;
        }

        $data['type'] = 'module';
        $data['name'] = $this->language->text('Module @name', array('@name' => $data['name']));

        $time = date('d-m-Y--G-i');
        $destination = gplcart_file_unique("$directory/module-{$data['id']}-$time.zip");
        $data['path'] = gplcart_file_relative_path($destination);

        $success = $this->zip->folder($data['directory'], $destination, $data['id']);

        if ($success) {
            return (bool) $model->add($data);
        }

        return false;
    }

}
