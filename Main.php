<?php

/**
 * @package Backup
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\backup;

use Exception;
use gplcart\core\Config,
    gplcart\core\Container;

/**
 * Main class for Backup module
 */
class Main
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->db = $config->getDb();
        $this->db->addScheme($this->getDbScheme());
    }

    /**
     * Implements hook "module.install.before"
     * @param null|string $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!class_exists('ZipArchive')) {
            $result = $this->getTranslationModel()->text('Class ZipArchive does not exist');
        } else {

            try {
                $this->db->importScheme('backup', $this->getDbScheme());
            } catch (Exception $ex) {
                $result = $ex->getMessage();
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
        return $this->getModel()->backup($handler_id, $data);
    }

    /**
     * Performs restore operation
     * @param string $handler_id
     * @param array $data
     * @return boolean|string
     */
    public function restore($handler_id, array $data)
    {
        return $this->getModel()->restore($handler_id, $data);
    }

    /**
     * Returns an array of defined handlers
     * @return array
     */
    public function getHandlers()
    {
        return $this->getModel()->getHandlers();
    }

    /**
     * Whether a backup already exists
     * @param string $id
     * @param null|string $version
     * @return bool
     */
    public function exists($id, $version = null)
    {
        return $this->getModel()->exists($id, $version);
    }

    /**
     * Returns backup model
     * @return \gplcart\modules\backup\models\Backup
     */
    protected function getModel()
    {
        return Container::get('gplcart\\modules\\backup\\models\\Backup');
    }

    /**
     * Translation UI model instance
     * @return \gplcart\core\models\Translation
     */
    protected function getTranslationModel()
    {
        return Container::get('gplcart\\core\\models\\Translation');
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
