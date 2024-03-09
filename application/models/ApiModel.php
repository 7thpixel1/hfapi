<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of ApiModel
 *
 * @author saqibahmad
 */
class ApiModel extends PIXEL_Model {
    public function __construct() {
        parent::__construct();
    }
    public function countDonations($object) {

        
        $this->db->select("count(a.id) as records")
                ->from("donations a")
                
                ->join("donors b", "a.donor_id=b.id", 'left')
                ->join("batch ba", "a.batch_id=ba.id", 'left')
                ->join("users d", "a.created_by=d.id", 'left')
                ->join("receipt c", "a.receipt_id=c.id", 'left')
                ->join("project p", "a.project_id=p.id", 'left')
                ->where("a.parent_id<>", 0)
                ->where("a.donor_id", $object->id);

        $query = $this->db->get();
        $row = $query->row(0);
        return $row->records;
    }
    public function donations($object) {
        $this->db->limit($this->limit, $this->start);
        $this->db->select("a.id, a.receipt_date, a.deposit_type, a.amount, a.non_eligible_amount, a.batch_id, a.status, a.parent_id AS donation_id, a.donor_id, a.eligible_amount,a.issuer_name,IFNULL(a.fee,0) AS fee, "
                        . "b.first_name, b.last_name, b.refrence_id,b.type, c.number, CONCAT(d.first_name,' ',d.last_name) AS auth_name, "
                        . "p.name as project_name, ba.batch_number, b.email,b.cell,b.address1,b.postal_code, ci.name as city_name, st.name as state, br.name as branch_name")
                ->from("donations a")
                ->join("donors b", "a.donor_id=b.id", 'left')
                ->join("batch ba", "a.batch_id=ba.id", 'left')
                ->join("receipt c", "a.receipt_id=c.id", 'left')
                ->join("users d", "a.created_by=d.id")
                ->join("project p", "a.project_id=p.id")
                ->join('cities ci', 'b.city_id=ci.id', 'left')
                ->join('provinces st', 'b.state_id=st.id', 'left')
                ->join('branches br', 'b.branch_id=br.id', 'left')
                ->where("a.parent_id<>", 0)
                ->where("a.donor_id", $object->id);
        $sort_column = 'a.id';
        $sort_dir = 'desc';
        $this->db->order_by($sort_column, $sort_dir);
        

        $query = $this->db->get();
        if ((int) $query->num_rows() > 0) {
            return $query->result();
        }
        return NULL;
    }
    public function getDonation($object) {
        
        $this->db->where('d.id', $object->id)
                ->where('d.donor_id', $object->donor_id)
                ->where('d.parent_id', Pixel::$ZERO);
        $this->db->select("d.*,IFNULL(c.name,'') AS city_name, ,IFNULL(p.name,'') AS province, ,IFNULL(cu.name,'') AS country")
                ->from("donations d")
                ->join('cities c', 'd.city_id=c.id', 'left')
                ->join('provinces p', 'd.state_id=p.id', 'left')
                ->join('countries cu', 'd.country_id=cu.id', 'left');
        $query = $this->db->get();
        if ((int) $query->num_rows() > 0) {
            return $query->row(0);
        }
        return NULL;
    }
    
    public function getDonor($donor_id) {
        $this->db->limit(1);
        
        $this->db->select('a.title , a.address1, a.address2, a.refrence_id, a.home_phone, a.id as value, a.email, a.postal_code, '
                . 'CONCAT(a.last_name, ", ", a.first_name)  as label, a.middle_name, a.last_name, a.first_name, '
                . 'b.name as city_name, c.name as province, d.name as country')
                ->from('donors a')
                ->join('cities b', 'a.city_id=b.id')
                ->join('provinces c', 'a.state_id=c.id')
                ->join('countries d', 'a.country_id=d.id')
                ->where('a.id', $donor_id);
        $records = $this->db->get();
        if($records->num_rows() > 0){
            return $records->row(0);
        }
        return NULL;
    }
    public function getReceipt($receipt_id) {
        $this->db->limit(1);        
        $this->db->select('a.id as value, a.number  as label, a.number, b.last_name, b.first_name')
                ->from('receipt a')
                ->join('users b', 'a.issued_to=b.id')
                ->where('a.id', $receipt_id);
        $records = $this->db->get();
        if($records->num_rows() > 0){
            return $records->row(0);
        }
        return NULL;
    }
    
    public function getChildren($id) {
        
        $this->db->select('d.amount, d.eligible_amount, non_eligible_amount, d.id, p.name, p.id as project_id')
                ->from('donations d')
                ->join('project p', 'd.project_id=p.id')
                ->where('d.parent_id', $id);
        $records = $this->db->get();
        if($records->num_rows() > 0){
            return $records->result();
        }
        return NULL;
    }
    
    public function listBranches() {
        
        $this->db->select("b.id, b.name, ifnull(p.name,'') as parent")
                ->from("branches b")
                ->join("branches p", "b.parent_id=p.id",'left')
                ->where('b.parent_id>', Pixel::$ZERO)
                ->order_by('b.id');
        
        $query = $this->db->get();
        if((int)$query->num_rows() > 0){
            return $query->result();
        }
        return NULL;
    
    }
    
    public function listProgram() {
        
        $this->db->select("id, name")
                ->from('program')
                ->order_by('name');
        
        $query = $this->db->get();
        if((int)$query->num_rows() > 0){
            return $query->result();
        }
        return NULL;
    
    }
    
    public function listProject($object) {
        $this->db->limit($this->limit, $this->start);
        
        $this->db->select("c.id, c.name, c.project_code,c.parent_id, IFNULL(p.name,'') AS parent_name, pr.name AS program_name")
                ->from("project c")
                ->join("program pr", "pr.id=c.program_id")
                ->join("project p", "c.parent_id=p.id", 'left')
                ->order_by("IFNULL(p.name,'a'), c.id")
                ->where('c.program_id', $object->program_id);
        if((int)$object->parent_id > Pixel::$ZERO){
            $this->db->where('c.parent_id', $object->parent_id);
        }

        $query = $this->db->get();
        if ((int) $query->num_rows() > 0) {
            return $query->result();
        }
        return NULL;
    }
    public function listFamily($id)
    {
        
        $this->db->select('id,title,first_name,last_name,middle_name,other_name,business_name,date_of_birth,'
                . 'address1,address2,city_id,state_id,country_id,postal_code,email,home_phone,cell,'
                . 'refrence_id as member_code,parent_id,'
                . '(select name from cities where id=donors.city_id) as city_name,'
                . '(select name from provinces where id=donors.state_id) as state_name,'
                . '(select name from countries where id=donors.country_id) as country_name,'
                . '(select name from branches where id=donors.branch_id) as branch_name,'
                . '(select name from select_types where type=\'gender\' and id=donors.gender) as gender')
                ->from('donors')
                ->where('status', Pixel::$YES);
        $this->db->group_start();
        $this->db->where('parent_id',  $id)
                ->or_where('id',  $id);
        $this->db->group_end();
        $records = $this->db->get();
        if($records->num_rows() > 0){
            return $records->result();
        }
        return NULL;
    }
    
    public function getCRAAnuualStatmentList($donor_id, $year) {
        $start = $year . "-01-01";
        $end = $year . "-12-31";
        $this->db->limit($this->limit, $this->start);
        $this->db->select("a.id, a.receipt_date, a.deposit_type, a. amount,a.non_eligible_amount,a.eligible_amount, a.batch_id,"
                        . "c.number,  YEAR(a.receipt_date) as year, ba.batch_number,"
                        . "(select GROUP_CONCAT(p.name) from donations b, project p where p.id=b.project_id and b.parent_id=a.id) as project_name")
                ->from("donations a")
                ->join("batch ba", "a.batch_id=ba.id")
                ->join("receipt c", "a.receipt_id=c.id")
                ->where("a.parent_id", 0)
                ->where("a.status", 0)
                ->where("a.donor_id", $donor_id)
                ->where("YEAR(a.receipt_date) in  (" . $year . ")", NULL)
                ->group_by("YEAR(a.receipt_date), a.id")
                ->order_by('YEAR(a.receipt_date)', "desc")
                ->order_by("a.receipt_date");
        $query = $this->db->get();
        if ((int) $query->num_rows() > 0) {
            return $query->result();
        }
        return NULL;
    }
}
