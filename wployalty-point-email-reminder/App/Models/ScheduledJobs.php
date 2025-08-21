<?php

namespace WLPR\App\Models;

use WLPR\App\Helpers\WC;
use Wlr\App\Models\Base;

defined('ABSPATH') or die();

class ScheduledJobs extends Base{
    function __construct(){
        parent::__construct();
        $this->table = self::$db->prefix . "wlr_scheduled_jobs";
        $this->primary_key = "id";
        $this->fields = [
            'uid' => '%d',
            'source_app' => '%s',
            'admin_mail' => '%s',
            'rule_name' => '%s',
            'category' => '%s',
            'action_type' => '%s',
            'conditions' => '%s',
            'status' => '%s',
            'limit' => '%d',
            'offset' => '%d',
            'last_processed_id' => '%d',
            'revert_enabled' => '%s',
            'revert_status' => '%s',
            'revert_offset' => '%d',
            'total_records' => '%d',
            'processed_records' => '%d',
            'failed_records' => '%d',
            'current_batch' => '%d',
            'total_batches' => '%d',
            'last_batch_processed_at' => '%d',
            'created_at' => '%d',
            'updated_at' => '%d',
        ];
    }

    public static function getJobByAction($action = ''){
        if (empty($action) || !is_string($action)) {
            return [];
        }
        global $wpdb;
        /* check job exist or not */
        $where = self::$db->prepare('id > %d AND source_app = %s AND category =%s', [
            0,
            'wlr_migration',
            $action
        ]);
        $scheduled_jobs = new ScheduledJobs();

        return $scheduled_jobs->getWhere($where);
    }

    public static function insertData($post) {
        if (empty($post) || !is_array($post)) {
            return 0;
        }
        $max_uid = $post['job_id'] ?? ScheduledJobs::getMaxUid();

        $admin_mail = WC::getLoginUserEmail();
        $conditions = [
            'update_point' => !empty($post['update_point']) ? $post['update_point'] : 'skip',
            'update_banned_user' => !empty($post['update_banned_user']) ? $post['update_banned_user'] : 'skip',
        ];
        $job_data = [
            'uid' => $max_uid,
            'source_app' => 'wlr_migration',
            'admin_mail' => $admin_mail,
            'category' => !empty($post['migration_action']) ? $post['migration_action'] : "",
            'action_type' => 'migration_to_wployalty',
            'conditions' => json_encode($conditions),
            'status' => 'pending',
            'limit' => 10, //TODO: Implement Settings helpeer (int) Settings::get('batch_limit', 10),
            'offset' => 0,
            'last_processed_id' => 0,
            'created_at' => strtotime(date('Y-m-d h:i:s')),
        ];
        $job_table_model = new ScheduledJobs();

        return $job_table_model->insertRow($job_data);
    }

    public static function getMaxUid()
    {
        $cron_job_modal = new ScheduledJobs();
        $where = self::$db->prepare(' id > %d', [0]);

        $data_job = $cron_job_modal->getWhere($where, 'MAX(uid) as max_uid');
        $max_uid = 1;
        if (!empty($data_job) && is_object($data_job) && isset($data_job->max_uid)) {
            $max_uid = $data_job->max_uid + 1;
        }

        return $max_uid;
    }

    public static function getAvailableJob(){
        $job_table = new ScheduledJobs();
        $where = self::$db->prepare("  source_app = %s AND id > 0 AND status IN (%s,%s) ORDER BY id ASC", [
            "wlr_migration",
            "pending",
            "processing"
        ]);

        return $job_table->getWhere($where);
    }

    function beforeTableCreation(){
        // Silence is golden
    }

    function runTableCreation(){
        $create_table_query = "CREATE TABLE IF NOT EXISTS {$this->table} (
				 `{$this->getPrimaryKey()}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				 `uid` BIGINT UNSIGNED NOT NULL,
				 `source_app` varchar(180) DEFAULT NULL,
				 `admin_mail` varchar(180) DEFAULT NULL,
				 `rule_name` varchar(255) DEFAULT NULL,
				 `category` varchar(180) DEFAULT NULL,
				 `action_type` varchar(180) DEFAULT NULL,
				 `conditions` longtext DEFAULT NULL,
				 `status` varchar(50) DEFAULT NULL,
				 `limit` INT(11) DEFAULT 0,
                 `offset` BIGINT DEFAULT NULL,
				 `last_processed_id` BIGINT DEFAULT 0,
  				 `revert_enabled` varchar(50) DEFAULT 'not_yet',             
                 `revert_status` varchar(50) DEFAULT 'not_yet',
                 `revert_offset` INT(11) DEFAULT NULL,
                 `total_records` BIGINT DEFAULT 0,
                 `processed_records` BIGINT DEFAULT 0,
                 `failed_records` BIGINT DEFAULT 0,
                 `current_batch` INT(11) DEFAULT 0,
                 `total_batches` INT(11) DEFAULT 0,
                 `last_batch_processed_at` BIGINT DEFAULT 0,
                 `created_at` BIGINT DEFAULT 0,
				 `updated_at` BIGINT DEFAULT 0,
				  PRIMARY KEY (`{$this->getPrimaryKey()}`),
                  UNIQUE KEY (`uid`))
				 ";
        $this->createTable($create_table_query);
    }

    function afterTableCreation(){
        $index_fields = array(
            "source_app",
            "category",
            "action_type",
            "status",
            "last_processed_id",
            "revert_enabled",
            "revert_status",
            "created_at"
        );
        $this->insertIndex($index_fields);
    }

    public static function getJobById($id = 0){
        if (empty($id) || !is_numeric($id)) {
            return [];
        }
        $job_table = new ScheduledJobs();
        $where = self::$db->prepare(" source_app = %s AND id = %d", ['wlr_email_reminder', $id]);

        return $job_table->getWhere($where);
    }

    /**
     * Get all email reminder rules
     */
    public static function getEmailReminderRules($page = 1, $per_page = 5, $search_term = '')
    {
        $job_table = new ScheduledJobs();
        $offset = ($page - 1) * $per_page;
        
        $where_conditions = ["source_app = %s", "action_type = %s"];
        $where_values = ['wlr_email_reminder', 'email_reminder'];
        
        // Add search condition if search term is provided
        if (!empty($search_term)) {
            $where_conditions[] = "rule_name LIKE %s";
            $where_values[] = '%' . $search_term . '%';
        }
        
        $where_clause = implode(' AND ', $where_conditions) . " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $where = self::$db->prepare($where_clause, $where_values);
        
        $results = $job_table->getWhere($where, '*', false);
        
        return $results;
    }

    public static function getEmailReminderRulesCount($search_term = '')
    {
        $job_table = new ScheduledJobs();
        
        $where_conditions = ["source_app = %s", "action_type = %s"];
        $where_values = ['wlr_email_reminder', 'email_reminder'];
        
        // Add search condition if search term is provided
        if (!empty($search_term)) {
            $where_conditions[] = "rule_name LIKE %s";
            $where_values[] = '%' . $search_term . '%';
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        $where = self::$db->prepare($where_clause, $where_values);
        
        $result = $job_table->getWhere($where, 'COUNT(*) as total', true);
        
        return isset($result->total) ? (int)$result->total : 0;
    }

    /**
     * Get email reminder rule by ID
     */
    public static function getEmailReminderRuleById($id = 0)
    {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }
        
        $job_table = new ScheduledJobs();
        $where = self::$db->prepare("id = %d AND source_app = %s AND action_type = %s", [
            $id,
            'wlr_email_reminder',
            'email_reminder'
        ]);
        
        $result = $job_table->getWhere($where, '*', false);
        
        return is_array($result) && !empty($result) ? $result[0] : null;
    }

    /**
     * Check if email reminder rules exist
     */
    public static function hasEmailReminderRules()
    {
        $rules = self::getEmailReminderRules();
        return !empty($rules);
    }

    /**
     * Insert email reminder rule
     */
    public static function insertEmailReminderRule($rule_data)
    {
        if (empty($rule_data) || !is_array($rule_data)) {
            return 0;
        }

        $max_uid = self::getMaxUid();
        $admin_mail = WC::getLoginUserEmail();
        
        // Determine category based on rule type
        $category = ($rule_data['type'] === 'admin') ? 'admin_report' : 'user_report';
        
        $job_data = [
            'uid' => $max_uid,
            'source_app' => 'wlr_email_reminder',
            'admin_mail' => $admin_mail,
            'category' => $category,
            'action_type' => 'email_reminder',
            'conditions' => json_encode($rule_data['conditions'] ?? []),
            'status' => $rule_data['status'] ?? 'pending',
            'limit' => $rule_data['batch_limit'] ?? 50,
            'offset' => 0,
            'last_processed_at' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $job_table_model = new ScheduledJobs();
        return $job_table_model->insertRow($job_data);
    }

    /**
     * Update email reminder rule
     */
    public static function updateEmailReminderRule($rule_id, $rule_data)
    {
        if (empty($rule_id) || empty($rule_data) || !is_array($rule_data)) {
            return false;
        }

        // Determine category based on rule type
        $category = ($rule_data['type'] === 'admin') ? 'admin_report' : 'user_report';
        
        $update_data = [
            'category' => $category,
            'conditions' => json_encode($rule_data['conditions'] ?? []),
            'status' => $rule_data['status'] ?? 'pending',
            'limit' => $rule_data['batch_limit'] ?? 50,
            'updated_at' => time(),
        ];

        $job_table_model = new ScheduledJobs();
        return $job_table_model->updateRow($update_data, $rule_id);
    }

    /**
     * Insert a new reminder rule
     * @param array $data
     * @return int|false Inserted ID or false
     */
    public function insertReminderRule($data) {
        return $this->insertRow($data);
    }

    /**
     * Get a single reminder rule by ID
     * @param int $id
     * @return object|null
     */
    public function getReminderRuleById($id) {
        return $this->getByKey($id);
    }

    /**
     * Get all reminder rules, optionally filtered by type (admin/user)
     * @param string|null $type
     * @return array
     */
    public function getAllReminderRules($type = null) {
        $where = "1=1";
        if ($type === 'admin') {
            $where .= " AND category = 'admin_report'";
        } elseif ($type === 'user') {
            $where .= " AND category = 'user_report'";
        }
        return $this->getWhere($where, '*', false);
    }

    /**
     * Update a reminder rule by ID
     * @param int $id
     * @param array $data
     * @return bool|int
     */
    public function updateReminderRule($id, $data) {
        return $this->updateRow($data, [ $this->primary_key => $id ]);
    }

    /**
     * Delete a reminder rule by ID
     * @param int $id
     * @return bool|int
     */
    public function deleteReminderRule($id) {
        return $this->deleteRow([ $this->primary_key => $id ]);
    }
}