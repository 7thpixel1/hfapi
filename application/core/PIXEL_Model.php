<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PIXEL_Model
 *
 * @author Saqib Ahmad
 */
class PIXEL_Model extends CI_Model {

    protected $limit, $start, $new_id, $currentUser, $currentDate, $_stock_row;
    public $table;

    public function __construct() {
        parent::__construct();
        $now = new DateTime();
        $this->currentDate = $now->format('Y-m-d H:i:s');
    }

    
    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function setStart($start) {
        $this->start = $start;
    }

    public function create($object) {

        if ($this->db->insert($this->table, (array) $object)) {
            return $this->new_id = $this->db->insert_id();
        }
        return FALSE;
    }

    public function update($object, $tmpId) {
        $this->db->where('id', $tmpId);
        $this->db->update($this->table, (array) $object);
        return $this->db->affected_rows();
    }

    public function findById($field, $value, $fields = "*") {
        $this->db->select($fields)
                ->from($this->table)
                ->where($field, $value);
        $query = $this->db->get();
        if ((int) $query->num_rows() > Pixel::$ZERO) {
            return $query->row(0);
        }
        return NULL;
    }

    public function checkRecordExists($field, $value, $id_field = '', $id = NULL) {
        $this->db->select("count(" . $id_field . ") as cnt");
        $this->db->from($this->table);
        $this->db->where($field, $value);
        if ((int) ($id ?? NULL) > Pixel::$ZERO) {
            $this->db->where($id_field . "<>", $id);
        }

        $query = $this->db->get();
        return $query->row(0)->cnt;
    }

    public function select($fields = "*") {
        $this->db->select($fields)
                ->from($this->table);
        $query = $this->db->get();
        if ((int) $query->num_rows() > Pixel::$ZERO) {
            return $query->result();
        }
        return NULL;
    }

    

}
