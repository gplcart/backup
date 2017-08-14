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
     */
    public function hookModuleInstallBefore(&$result)
    {
        $language = $this->getLanguage();

        if (!class_exists('ZipArchive')) {
            $result = $language->text('Class ZipArchive does not exist');
            return null;
        }

        if ($this->db->tableExists('backup')) {
            $result = $language->text('Table "backup" already exists');
            return null;
        }

        if (!$this->db->import($this->getDbScheme())) {
            $this->db->deleteTable('backup');
            $result = $language->text('An error occurred while importing database tables');
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
        $permissions['backup'] = 'Backup: access';
        $permissions['backup_delete'] = 'Backup: delete';
        $permissions['backup_download'] = 'Backup: download';
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/report/backup'] = array(
            'access' => 'backup',
            'menu' => array('admin' => 'Backups'),
            'handlers' => array(
                'controller' => array('gplcart\\modules\\backup\\controllers\\Backup', 'listBackup')
            )
        );
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
                    'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
                    'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
                    'path' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
                    'user_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
                    'version' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
                    'module_id' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => '')
                )
            )
        );
    }

}
