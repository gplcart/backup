<?php

/**
 * @package Backup
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\backup\handlers;

use gplcart\core\helpers\Zip as ZipHelper;
use gplcart\core\models\Translation as TranslationModel;

/**
 * Provides methods to backup modules
 */
class Module
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Zip helper class instance
     * @var \gplcart\core\helpers\Zip $zip
     */
    protected $zip;

    /**
     * @param TranslationModel $translation
     * @param ZipHelper $zip
     */
    public function __construct(TranslationModel $translation, ZipHelper $zip)
    {
        $this->zip = $zip;
        $this->translation = $translation;
    }

    /**
     * Creates a module backup
     * @param array $data
     * @param \gplcart\modules\backup\models\Backup $model
     * @return boolean
     */
    public function backup(array $data, $model)
    {
        $directory = gplcart_file_private_module('backup');
        if (!file_exists($directory) && !mkdir($directory, 0775, true)) {
            return false;
        }

        $data['type'] = 'module';
        $data['name'] = $this->translation->text('Module @name', array('@name' => $data['name']));

        $time = date('d-m-Y--G-i');
        $destination = gplcart_file_unique("$directory/module-{$data['id']}-$time.zip");
        $data['path'] = gplcart_file_relative($destination);

        $success = $this->zip->directory($data['directory'], $destination, $data['id']);

        if ($success) {
            return (bool) $model->add($data);
        }

        return false;
    }

}
