<?php

/**
 * @package Backup
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\backup;

use gplcart\core\Module;

/**
 * Main class for Backup module
 */
class Backup extends Module
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->db->addScheme($this->getDbScheme());
    }

    /**
     * Implements hook "module.install.before"
     * @param null|string $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!class_exists('ZipArchive')) {
            $result = $this->getLanguage()->text('Class ZipArchive does not exist');
        } else {

            $result_db = $this->installDbTable('backup', $this->getDbScheme());

            if ($result_db !== true) {
                $result = $result_db;
            }
        }
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->db->deleteTable('backup');
    }

    /**
     * Implements hook "user.role.permissions"
     * @param array $permissions
     */
    public function hookUserRolePermissions(array &$permissions)
    {
        $permissions['backup'] = /* @text */'Backup: access';
        $permissions['backup_delete'] = /* @text */'Backup: delete';
        $permissions['backup_download'] = /* @text */'Backup: download';
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/report/backup'] = array(
            'access' => 'backup',
            'menu' => array('admin' => /* @text */'Backups'),
            'handlers' => array(
                'controller' => array('gplcart\\modules\\backup\\controllers\\Backup', 'listBackup')
            )
        );
    }

    /**
     * Performs backup operation
     * @param string $handler_id
     * @param array $data
     * @return boolean|string
     */
    public function backup($handler_id, array $data)
    {
        return $this->getModelBackup()->backup($handler_id, $data);
    }

    /**
     * Performs restore operation
     * @param string $handler_id
     * @param array $data
     * @return boolean|string
     */
    public function restore($handler_id, array $data)
    {
        return $this->getModelBackup()->restore($handler_id, $data);
    }

    /**
     * Returns an array of defined handlers
     * @return array
     */
    public function getHandlers()
    {
        return $this->getModelBackup()->getHandlers();
    }

    /**
     * Whether a backup already exists
     * @param string $id
     * @param null|string $version
     * @return bool
     */
    public function exists($id, $version = null)
    {
        return $this->getModelBackup()->exists($id, $version);
    }

    /**
     * Returns backup model
     * @return \gplcart\modules\backup\models\Backup
     */
    protected function getModelBackup()
    {
        return $this->getModel('Backup', 'backup');
    }

    /**
     * Returns an array of database scheme
     * @return array
     */
    protected function getDbScheme()
    {
        return array(
            'backup' => array(
                'fields' => array(
                    'backup_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
                    'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
                    'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
                    'path' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
                    'user_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
                    'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
                    'version' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
                    'id' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => '')
                )
            )
        );
    }

}
