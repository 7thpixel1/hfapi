<?php

namespace App\Models;

use App\Config\Database;

class PixelModel {

    protected $db;
    protected $limit, $offset, $start, $new_id, $currentUser, $currentDate, $_stock_row;
    public $table;

    public function __construct(Database $database) {
        $this->db = $database;
        $now = new \DateTime();
        $this->currentDate = $now->format('Y-m-d H:i:s');
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function setOffset($page) {
        $this->offset = ((int) $page - 1) * $this->limit;
    }

    public function setStart($start) {
        $this->offset = $start;
    }

    public function create($object) {
        $fields = implode(", ", array_keys($object));
        $placeholders = ":" . implode(", :", array_keys($object));
        $sql = "INSERT INTO $this->table ($fields) VALUES ($placeholders)";
        $stmt = $this->db->query($sql);

        if ($stmt->execute($object)) {
            return $this->new_id = $this->db->lastInsertId();
        }
        return false;
    }

    public function update($object, $tmpId) {
        $set = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($object)));
        $sql = "UPDATE $this->table SET $set WHERE id = :id";
        $object['id'] = $tmpId;
        $stmt = $this->db->query($sql);
        return $stmt->execute($object);
    }

    public function findById($field, $value, $fields = "*") {
        $sql = "SELECT $fields FROM $this->table WHERE $field = :value";
        $this->db->query($sql, ['value' => $value]);
        return $this->db->fetch();
    }

    public function checkRecordExists($field, $value, $id_field = '', $id = null) {
        $sql = "SELECT COUNT($id_field) as cnt FROM $this->table WHERE $field = :value";
        if ($id) {
            $sql .= " AND $id_field <> :id";
        }
        $stmt = $this->db->query($sql);
        $stmt->execute(['value' => $value, 'id' => $id]);
        return $stmt->fetchColumn();
    }

    public function select($fields = "*") {
        $sql = "SELECT $fields FROM $this->table";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function isAuthorized($username, $password) {
        $sql = "SELECT * FROM donors WHERE email = :username AND status = :status AND can_login = :can_login";
        
        $params = [
            ':username' => $username,
            ':status' => 1,
            ':can_login' => 1
        ];
        $this->db->query($sql, $params);
        $user = $this->db->fetch();
        
        if ($user && $this->verifyPassword($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
        return null;
    }

    private function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    private function interpolateQuery($query, $params) {
        foreach ($params as $key => $value) {
            $query = str_replace($key, "'" . $value . "'", $query);
        }
        return $query;
    }

    /* Api Methods */

    public function countDonations($object) {
        $sql = "SELECT COUNT(a.id) AS records 
                FROM donations a
                LEFT JOIN donors b ON a.donor_id = b.id
                LEFT JOIN batch ba ON a.batch_id = ba.id
                LEFT JOIN users d ON a.created_by = d.id
                LEFT JOIN receipt c ON a.receipt_id = c.id
                LEFT JOIN project p ON a.project_id = p.id
                WHERE a.parent_id <> 0 AND a.donor_id = :donor_id";

        $stmt = $this->db->query($sql);
        $stmt->execute(['donor_id' => $object->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['records'] : 0;
    }

    public function donations($object) {
        $sql = "SELECT a.id, a.receipt_date, a.deposit_type, a.amount, a.non_eligible_amount, 
                        a.batch_id, a.status, a.parent_id AS donation_id, a.donor_id, 
                        a.eligible_amount, a.issuer_name, IFNULL(a.fee, 0) AS fee, 
                        b.first_name, b.last_name, b.refrence_id, b.type, 
                        c.number, CONCAT(d.first_name, ' ', d.last_name) AS auth_name, 
                        p.name AS project_name, ba.batch_number, b.email, b.cell, 
                        b.address1, b.postal_code, ci.name AS city_name, 
                        st.name AS state, br.name AS branch_name 
                FROM donations a
                LEFT JOIN donors b ON a.donor_id = b.id
                LEFT JOIN batch ba ON a.batch_id = ba.id
                LEFT JOIN receipt c ON a.receipt_id = c.id
                LEFT JOIN users d ON a.created_by = d.id
                LEFT JOIN project p ON a.project_id = p.id
                LEFT JOIN cities ci ON b.city_id = ci.id
                LEFT JOIN provinces st ON b.state_id = st.id
                LEFT JOIN branches br ON b.branch_id = br.id
                WHERE a.parent_id <> 0 AND a.donor_id = :donor_id
                ORDER BY a.id DESC
                LIMIT :offset, :limit";

        $params = [
            ':donor_id' => $object->id,
            ':offset' => (int) $this->offset,
            ':limit' => (int) $this->limit
        ];
        $this->db->query($sql, $params);
        return $this->db->fetchAll() ?: null;
    }

    public function getDonation($object) {
        $sql = "SELECT d.*, IFNULL(c.name, '') AS city_name, IFNULL(p.name, '') AS province, 
                        IFNULL(cu.name, '') AS country 
                FROM donations d
                LEFT JOIN cities c ON d.city_id = c.id
                LEFT JOIN provinces p ON d.state_id = p.id
                LEFT JOIN countries cu ON d.country_id = cu.id
                WHERE d.id = :id AND d.donor_id = :donor_id AND d.parent_id = :parent_id";
        $params = [
            'id' => $object->id,
            'donor_id' => $object->donor_id,
            'parent_id' => 0 // Assuming Pixel::$ZERO is 0
        ];
        $this->db->query($sql, $params);
        return $this->db->fetchObject() ?: null;
    }

    public function getDonor($donor_id) {
        $sql = "SELECT a.title, a.address1, a.address2, a.refrence_id, a.home_phone, 
                        a.id AS value, a.email, a.postal_code, 
                        CONCAT(a.last_name, ', ', a.first_name) AS label, 
                        a.middle_name, a.last_name, a.first_name, 
                        b.name AS city_name, c.name AS province, d.name AS country 
                FROM donors a
                JOIN cities b ON a.city_id = b.id
                JOIN provinces c ON a.state_id = c.id
                JOIN countries d ON a.country_id = d.id
                WHERE a.id = :donor_id 
                LIMIT 1";

        $this->db->query($sql, ['donor_id' => $donor_id]);
        return $this->db->fetchObject() ?: null;
        
    }

    public function getReceipt($receipt_id) {
        $sql = "SELECT a.id AS value, a.number AS label, a.number, b.last_name, b.first_name 
                FROM receipt a
                JOIN users b ON a.issued_to = b.id
                WHERE a.id = :receipt_id 
                LIMIT 1";

        
        $this->db->query($sql, ['receipt_id' => $receipt_id]);
        return $this->db->fetchObject() ?: null;
        
    }

    public function getChildren($id) {
        $sql = "SELECT d.amount, d.eligible_amount, d.non_eligible_amount, d.id, p.name, p.id AS project_id 
                FROM donations d
                JOIN project p ON d.project_id = p.id
                WHERE d.parent_id = :parent_id";

        $this->db->query($sql, ['parent_id' => $id]);
        return $this->db->fetchAllObjects() ?: null;
    }

    public function listBranches() {
        $sql = "SELECT b.id, b.name, IFNULL(p.name, '') AS parent 
                FROM branches b
                LEFT JOIN branches p ON b.parent_id = p.id
                WHERE b.parent_id > 0 
                ORDER BY b.id";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function listProgram() {
        $sql = "SELECT id, name 
                FROM program 
                ORDER BY name";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function listProject($object) {
        $sql = "SELECT c.id, c.name, c.project_code, c.parent_id, 
                        IFNULL(p.name, '') AS parent_name, pr.name AS program_name 
                FROM project c
                JOIN program pr ON pr.id = c.program_id
                LEFT JOIN project p ON c.parent_id = p.id
                WHERE c.program_id = :program_id";

        if ((int) $object->parent_id > 0) {
            $sql .= " AND c.parent_id = :parent_id";
        }

        $sql .= " ORDER BY IFNULL(p.name, 'a'), c.id
                  LIMIT :offset, :limit";

        $stmt = $this->db->query($sql);
        $stmt->bindValue(':program_id', $object->program_id);
        if ((int) $object->parent_id > 0) {
            $stmt->bindValue(':parent_id', $object->parent_id);
        }
        $stmt->bindValue(':offset', (int) $this->offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $this->limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function listFamily($id) {
        $sql = "SELECT id, title, first_name, last_name, middle_name, other_name, business_name, 
                        date_of_birth, address1, address2, city_id, state_id, country_id, 
                        postal_code, email, home_phone, cell, refrence_id AS member_code, 
                        parent_id, 
                        (SELECT name FROM cities WHERE id = donors.city_id) AS city_name,
                        (SELECT name FROM provinces WHERE id = donors.state_id) AS state_name,
                        (SELECT name FROM countries WHERE id = donors.country_id) AS country_name,
                        (SELECT name FROM branches WHERE id = donors.branch_id) AS branch_name,
                        (SELECT name FROM select_types WHERE type = 'gender' AND id = donors.gender) AS gender 
                FROM donors
                WHERE status = 1 AND (parent_id = :parent_id OR id = :id";

        $stmt = $this->db->query($sql);
        $stmt->execute(['parent_id' => $id, 'id' => $id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function saveDonation($data) {
        try {
            $this->db->beginTransaction();
            $sql = "INSERT INTO donations (amount, eligible_amount, non_eligible_amount, 
                            created_by, donor_id, receipt_id, project_id, 
                            batch_id, status, issuer_name, parent_id) 
                    VALUES (:amount, :eligible_amount, :non_eligible_amount, 
                            :created_by, :donor_id, :receipt_id, 
                            :project_id, :batch_id, :status, :issuer_name, :parent_id)";

            $stmt = $this->db->query($sql);
            $stmt->execute([
                'amount' => $data['amount'],
                'eligible_amount' => $data['eligible_amount'],
                'non_eligible_amount' => $data['non_eligible_amount'],
                'created_by' => $data['created_by'],
                'donor_id' => $data['donor_id'],
                'receipt_id' => $data['receipt_id'],
                'project_id' => $data['project_id'],
                'batch_id' => $data['batch_id'],
                'status' => $data['status'],
                'issuer_name' => $data['issuer_name'],
                'parent_id' => $data['parent_id']
            ]);

            $this->db->commit();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e; // re-throw the exception for handling upstream
        }
    }

    public function updateDonation($data) {
        try {
            $this->db->beginTransaction();
            $sql = "UPDATE donations SET amount = :amount, 
                            eligible_amount = :eligible_amount, 
                            non_eligible_amount = :non_eligible_amount, 
                            updated_by = :updated_by, 
                            receipt_id = :receipt_id, 
                            project_id = :project_id, 
                            batch_id = :batch_id, 
                            status = :status, 
                            issuer_name = :issuer_name 
                    WHERE id = :id AND donor_id = :donor_id";

            $stmt = $this->db->query($sql);
            $stmt->execute([
                'amount' => $data['amount'],
                'eligible_amount' => $data['eligible_amount'],
                'non_eligible_amount' => $data['non_eligible_amount'],
                'updated_by' => $data['updated_by'],
                'receipt_id' => $data['receipt_id'],
                'project_id' => $data['project_id'],
                'batch_id' => $data['batch_id'],
                'status' => $data['status'],
                'issuer_name' => $data['issuer_name'],
                'id' => $data['id'],
                'donor_id' => $data['donor_id']
            ]);

            $this->db->commit();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e; // re-throw the exception for handling upstream
        }
    }
}

?>