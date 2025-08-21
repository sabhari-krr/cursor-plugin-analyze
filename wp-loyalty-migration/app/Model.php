<?php

namespace WPLoyalty\Migration\App;

defined('ABSPATH') or exit;

abstract class Model
{
    protected $table;
    protected $primary_key = 'id';
    protected $fillable = [];
    protected $timestamps = true;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . $this->table;
    }

    /**
     * Create table query
     */
    abstract public function createTable();

    /**
     * Delete table query
     */
    abstract public function deleteTable();

    /**
     * Execute database query
     */
    public function executeDatabaseQuery($query)
    {
        global $wpdb;
        return $wpdb->query($query);
    }

    /**
     * Get all records
     */
    public function all()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table}");
    }

    /**
     * Find by ID
     */
    public function find($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE {$this->primary_key} = %d", $id));
    }

    /**
     * Insert record
     */
    public function insert($data)
    {
        global $wpdb;
        
        if ($this->timestamps) {
            $data['created_at'] = current_time('mysql');
            $data['updated_at'] = current_time('mysql');
        }

        $result = $wpdb->insert($this->table, $data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Update record
     */
    public function update($id, $data)
    {
        global $wpdb;
        
        if ($this->timestamps) {
            $data['updated_at'] = current_time('mysql');
        }

        return $wpdb->update(
            $this->table,
            $data,
            [$this->primary_key => $id]
        );
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        global $wpdb;
        return $wpdb->delete(
            $this->table,
            [$this->primary_key => $id]
        );
    }

    /**
     * Where clause
     */
    public function where($column, $value, $operator = '=')
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} {$operator} %s",
            $value
        ));
    }

    /**
     * Count records
     */
    public function count()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }
}