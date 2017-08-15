<?php

/**
 * @package Backup
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\backup\models;

use gplcart\core\Model,
    gplcart\core\Handler;
use gplcart\core\models\User as UserModel,
    gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to Backup model
 */
class Backup extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * @param UserModel $user
     * @param LanguageModel $language
     */
    public function __construct(UserModel $user, LanguageModel $language)
    {
        parent::__construct();

        $this->user = $user;
        $this->language = $language;
    }

    /**
     * Returns an array of backups or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT b.*, u.name AS user_name';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(b.backup_id)';
        }

        $sql .= ' FROM backup b'
                . ' LEFT JOIN user u ON(b.user_id = u.user_id)'
                . ' WHERE b.backup_id > 0';

        $where = array();

        if (isset($data['user_id'])) {
            $sql .= ' AND b.user_id = ?';
            $where[] = $data['user_id'];
        }

        if (isset($data['module_id'])) {
            $sql .= ' AND b.module_id = ?';
            $where[] = $data['module_id'];
        }

        if (isset($data['name'])) {
            $sql .= ' AND b.name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'user_id', 'version',
            'module_id', 'backup_id', 'type', 'created');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)
        ) {
            $sql .= " ORDER BY b.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY b.created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $results = $this->db->fetchAll($sql, $where, array('index' => 'backup_id'));
        $this->hook->attach('module.backup.list', $results, $this);
        return $results;
    }

    /**
     * Adds a backup to the database
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->attach('module.backup.add.before', $data, $this);

        if (empty($data)) {
            return false;
        }

        if (empty($data['user_id'])) {
            $data['user_id'] = $this->user->getId();
        }

        $data['created'] = GC_TIME;
        $data['backup_id'] = $this->db->insert('backup', $data);

        $this->hook->attach('module.backup.add.after', $data, $this);
        return $data['backup_id'];
    }

    /**
     * Loads a backup from the database
     * @param integer $id
     * @return array
     */
    public function get($id)
    {
        $sql = 'SELECT * FROM backup WHERE backup_id=?';
        return $this->db->fetch($sql, array($id));
    }

    /**
     * Deletes a backup from disk and database
     * @param integer $id
     * @return boolean
     */
    public function delete($id)
    {
        $this->hook->attach('module.backup.delete.before', $id, $this);

        if (empty($id)) {
            return false;
        }

        $result = (bool) $this->db->delete('backup', array('backup_id' => $id));

        if ($result) {
            $this->deleteZip($id);
        }

        $this->hook->attach('module.backup.delete.after', $id, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a backup ZIP archive
     * @param integer $backup_id
     * @return boolean
     */
    protected function deleteZip($backup_id)
    {
        $backup = $this->get($backup_id);

        if (isset($backup['path']) && file_exists(GC_FILE_DIR . "/{$backup['path']}")) {
            return unlink(GC_FILE_DIR . "/{$backup['path']}");
        }

        return false;
    }

    /**
     * Performs backup operation
     * @param string $handler_id
     * @param array $data
     * @return boolean|string
     */
    public function backup($handler_id, $data)
    {
        $handlers = $this->getHandlers();
        return Handler::call($handlers, $handler_id, 'backup', array($data, $this));
    }

    /**
     * Performs restore operation
     * @param string $handler_id
     * @param array $data
     * @return boolean|string
     */
    public function restore($handler_id, $data)
    {
        $handlers = $this->getHandlers();
        return Handler::call($handlers, $handler_id, 'restore', array($data, $this));
    }

    /**
     * Returns an array of backup handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_static(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = $this->getDefaultHandlers();
        $this->hook->attach('module.backup.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Returns a single handler
     * @param string $handler_id
     * @return array
     */
    public function getHandler($handler_id)
    {
        $handlers = $this->getHandlers();
        return empty($handlers[$handler_id]) ? array() : $handlers[$handler_id];
    }

    /**
     * Returns an array of default backup handlers
     * @return array
     */
    protected function getDefaultHandlers()
    {
        $handlers = array();

        $handlers['module'] = array(
            'name' => $this->language->text('Module'),
            'handlers' => array(
                'backup' => array('gplcart\\modules\\backup\\handlers\\Module', 'backup')
        ));

        return $handlers;
    }

}
