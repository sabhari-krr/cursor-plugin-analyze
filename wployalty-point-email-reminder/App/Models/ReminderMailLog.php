<?php

namespace WLPR\App\Models;

use Wlr\App\Models\Base;

class ReminderMailLog extends Base{
    function __construct(){
        parent::__construct();
        $this->table = self::$db->prefix . "wlr_reminder_mail_log";
        $this->primary_key = "id";
        $this->fields = [
            'job_id' => '%d',
            'action' => '%s',
            'user_email' => '%s',
            'note' => '%s',
            'batch_number' => '%d',
            'email_status' => '%s',
            'error_message' => '%s',
            'sent_at' => '%d',
            'created_at' => '%d',
            'updated_at' => '%d',
        ];
    }

    function beforeTableCreation(){
        // Silence is golden
    }
    
    function runTableCreation(){
        $create_table_query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            `{$this->getPrimaryKey()}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `job_id` BIGINT UNSIGNED NOT NULL,
            `action` varchar(180) DEFAULT NULL,
            `user_email` varchar(180) DEFAULT NULL,
            `note` varchar(180) DEFAULT NULL,
            `batch_number` BIGINT UNSIGNED NOT NULL,
            `email_status` varchar(180) DEFAULT NULL,
            `error_message` varchar(180) DEFAULT NULL,
            `sent_at` BIGINT DEFAULT 0,
            `created_at` BIGINT DEFAULT 0,
            `updated_at` BIGINT DEFAULT 0,
            PRIMARY KEY (`{$this->getPrimaryKey()}`),
            UNIQUE KEY (`job_id`)
        )";

        $this->createTable($create_table_query);
    }

    function afterTableCreation(){
        $index_fields = [
            'job_id',
            'action',
            'user_email',
            'created_at'
        ];
        $this->insertIndex($index_fields);
    }

    public function getSingleActivityBy($type = '', $email = ''){
        if(empty($type) || !is_string($type) || !in_array($type, ['job_id', 'action', 'user_email'])){
            return [];
        }
        $where = self::$db->prepare(" action = %s AND user_email = %s", [$type, $email]);
        $reminder_mail_log = new ReminderMailLog();

        return $reminder_mail_log->getWhere($where);
    }

    /**
     * Get the count of logs for a given action and job_id
     * 
     * @param mixed $action
     * @param mixed $job_id
     * @return int
     */
    public function getLogsCount($action = '', $job_id = 0){
        if(empty($job_id) || empty($action) || $job_id <= 0 || !is_string($action)){
            return 0;
        }
        $reminder_mail_log = new ReminderMailLog();
        $query = self::$db->prepare(" id > 0 AND job_id = %d and action = %s", [
            (int)$job_id,
            $action
        ]);
        $log_count = $reminder_mail_log->getWhere($query, 'COUNT(*) as total_count', true);

        return !empty($log_count) && isset($log_count->total_count) ? (int)$log_count->total_count : 0;
    }
}