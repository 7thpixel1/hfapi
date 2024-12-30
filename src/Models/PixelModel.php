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
        $this->offset = ((int) $this->offset <= 0) ? 0 : $this->offset;
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

        $sql = "SELECT id,title,first_name,last_name,middle_name,business_name,gender,address1,address2,postal_code,cell,branch_id,refrence_id,status,parent_id,city,state,country,username,date_of_birth, password_hash"
                . " FROM donors WHERE email = :username AND status = :status AND can_login = :can_login";

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

        $sql = "SELECT a.id,a.parent_id, a.receipt_date, a.deposit_type, a.amount, a.non_eligible_amount, a.batch_id, a.status, a.parent_id AS donation_id, a.donor_id, 
                        a.eligible_amount, a.issuer_name, IFNULL(a.fee, 0) AS fee, b.first_name, b.last_name, b.refrence_id, b.type, 
                        c.number, CONCAT(d.first_name, ' ', d.last_name) AS auth_name, p.name AS project_name, ba.batch_number, b.email, b.cell, 
                        b.address1, b.postal_code, a.city AS city_name, a.state, br.name AS branch_name 

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
                ORDER BY a.id DESC LIMIT " . (int) $this->offset . ", " . (int) $this->limit;

        $params = [
            ':donor_id' => $object->id
        ];
        //echo $this->printCompileQuery($sql, $params);
        $this->db->query($sql, $params);
        return $this->db->fetchAll() ?: null;
    }

    public function donationsCount($object) {
        $sql = "SELECT count(a.id) as cnt
                FROM donations a
                LEFT JOIN donors b ON a.donor_id = b.id
                LEFT JOIN batch ba ON a.batch_id = ba.id
                LEFT JOIN receipt c ON a.receipt_id = c.id
                LEFT JOIN users d ON a.created_by = d.id
                LEFT JOIN project p ON a.project_id = p.id
                LEFT JOIN branches br ON b.branch_id = br.id
                WHERE a.parent_id <> 0 AND a.donor_id = :donor_id";

        $params = [
            ':donor_id' => $object->id
        ];
        //echo $this->printCompileQuery($sql, $params);
        $this->db->query($sql, $params);
        $result = $this->db->fetchObject();

        return (int) $result->cnt;
    }

    public function printCompileQuery($sql, $params) {
        foreach ($params as $key => $value) {
            // Escape strings to prevent SQL syntax issues
            $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            $sql = str_replace($key, $escapedValue, $sql);
        }
        return $sql;
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
        $sql = "SELECT id,title,first_name,last_name,middle_name,business_name,
            gender,address1,address2,postal_code,cell,branch_id,refrence_id,status,parent_id,
            city,state,country,username,date_of_birth,CONCAT(last_name, ', ', first_name) AS label
                FROM donors
                WHERE id = :donor_id 
                LIMIT 1";

        $this->db->query($sql, ['donor_id' => $donor_id]);
        return $this->db->fetchObject() ?: null;
    }


    public function getDonorByUsername($email) {
        $sql = "SELECT id,title,first_name,last_name,middle_name,business_name,
            gender,address1,address2,postal_code,cell,branch_id,refrence_id,status,parent_id,
            city,state,country,username,date_of_birth,CONCAT(last_name, ', ', first_name) AS label
                FROM donors 
                WHERE (username = :email) and can_login = 1
                LIMIT 1";

        $this->db->query($sql, ['email' => $email]);
        return $this->db->fetchObject() ?: null;
    }

    public function getDonorByProvider($provider, $provider_id) {
        $sql = "SELECT id,title,first_name,last_name,middle_name,business_name,
            gender,address1,address2,postal_code,cell,branch_id,refrence_id,status,parent_id,
            city,state,country,username,date_of_birth,CONCAT(last_name, ', ', first_name) AS label
                FROM donors 
                WHERE (provider_id = :provider_id and provider = :provider)
                LIMIT 1";

        $this->db->query($sql, ['provider_id' => $provider_id, "provider" => $provider]);
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
            $params = [
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
            ];
            $this->db->query($sql, $params);
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
            $params = [
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
            ];
            $this->db->query($sql, $params);

            $this->db->commit();
            return $this->db->rowCount();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e; // re-throw the exception for handling upstream
        }
    }

    /* Payment */

    public function saveTransactionHistory(array $transactionData): void {
        $sql = "INSERT INTO global_payment_history (
                    donor_id, donation_id, auth_amount, avail_balance, avs_code, balance_amt, 
                    batch_ref, card_type, card_last4, trans_type, ref_num, resp_code, resp_msg, 
                    date_created, trans_auth_code, trans_id, fraud_mode, fraud_result, fraud_rule_1_key, 
                    fraud_rule_1_desc, fraud_rule_1_result, card_result, card_cvv_result, card_last4_detail, 
                    card_brand, card_avs_code, meta_info
                ) VALUES (
                    :donor_id, :donation_id, :auth_amount, :avail_balance, :avs_code, :balance_amt, 
                    :batch_ref, :card_type, :card_last4, :trans_type, :ref_num, :resp_code, :resp_msg, 
                    :timestamp, :trans_auth_code, :trans_id, :fraud_mode, :fraud_result, :fraud_rule_1_key, 
                    :fraud_rule_1_desc, :fraud_rule_1_result, :card_result, :card_cvv_result, :card_last4_detail, 
                    :card_brand, :card_avs_code, :meta_info
                )";
        // Binding the data to the prepared statement
        $params = [
            ':donor_id' => $transactionData['donorId'],
            ':donation_id' => $transactionData['donationId'],
            ':auth_amount' => $transactionData['authorizedAmount'] ?? null,
            ':avail_balance' => $transactionData['availableBalance'] ?? null,
            ':avs_code' => $transactionData['avsResponseCode'] ?? null,
            ':balance_amt' => $transactionData['balanceAmount'] ?? null,
            ':batch_ref' => $transactionData['batchSummary_batchReference'] ?? null,
            ':card_type' => $transactionData['cardType'] ?? null,
            ':card_last4' => $transactionData['cardLast4'] ?? null,
            ':trans_type' => $transactionData['originalTransactionType'] ?? null,
            ':ref_num' => $transactionData['referenceNumber'] ?? null,
            ':resp_code' => $transactionData['responseCode'] ?? null,
            ':resp_msg' => $transactionData['responseMessage'] ?? null,
            ':timestamp' => $transactionData['timestamp'] ?? null,
            ':trans_auth_code' => $transactionData['transactionReference_authCode'] ?? null,
            ':trans_id' => $transactionData['transactionReference_transactionId'] ?? null,
            ':fraud_mode' => $transactionData['fraudFilterResponse_fraudResponseMode'] ?? null,
            ':fraud_result' => $transactionData['fraudFilterResponse_fraudResponseResult'] ?? null,
            ':fraud_rule_1_key' => $transactionData['fraudFilterResponse_fraudResponseRules_0_key'] ?? null,
            ':fraud_rule_1_desc' => $transactionData['fraudFilterResponse_fraudResponseRules_0_description'] ?? null,
            ':fraud_rule_1_result' => $transactionData['fraudFilterResponse_fraudResponseRules_0_result'] ?? null,
            ':card_result' => $transactionData['cardIssuerResponse_result'] ?? null,
            ':card_cvv_result' => $transactionData['cardIssuerResponse_cvvResult'] ?? null,
            ':card_last4_detail' => $transactionData['cardDetails_maskedNumberLast4'] ?? null,
            ':card_brand' => $transactionData['cardDetails_brand'] ?? null,
            ':card_avs_code' => $transactionData['cardDetails_avsResponseCode'] ?? null,
            ':meta_info' => $transactionData['meta_info'] ?? null,
        ];
        $this->db->query($sql, $params);
    }

    public function saveDonor($donor) {
        try {
            // Start a transaction
            $this->db->beginTransaction();
            $sql = "INSERT INTO donors (title, first_name, last_name, business_name, date_of_birth, gender, address1, address2, city, state, country, postal_code, email, 
                cell, type, source, branch_id, refrence_id, created_date, created_by, status, password_hash, can_login, email_status, last_login, meta_info) 
                    VALUES (:title, :first_name, :last_name, :business_name, :date_of_birth, :gender, :address1, :address2, :city, :state, :country, :postal_code, :email, 
                    :cell, :type, :source, :branch_id, :refrence_id, :created_date, :created_by, :status, :password_hash, :can_login, :email_status, :last_login, :meta_info)";

            $params = [
                'title' => $donor->title,
                'first_name' => $donor->first_name,
                'last_name' => $donor->last_name,
                'business_name' => $donor->business_name,
                'date_of_birth' => $donor->date_of_birth ?? NULL,
                'gender' => $donor->gender,
                'address1' => $donor->address1,
                'address2' => $donor->address2,
                'city' => $donor->city,
                'state' => $donor->state,
                'country' => $donor->country,
                'postal_code' => $donor->postal_code,
                'email' => $donor->email,
                'cell' => $donor->cell,
                'type' => $donor->type,
                'source' => '2',
                'branch_id' => 114,
                'refrence_id' => $this->getMaxDonorId(),
                'created_date' => date('Y-m-d H:i:s'),
                'created_by' => 1,
                'status' => $donor->status,
                'password_hash' => $donor->password_hash,
                'can_login' => 1,
                'email_status' => 0,
                'last_login' => date('Y-m-d H:i:s'),
                'meta_info' => $donor->meta_info
            ];

            // Execute the query
            $this->db->query($sql, $params);
            $donor->id = $this->id = $this->db->lastInsertId();
            // Commit the transaction
            $this->db->commit();

            // Set the ID of the donor object
            
            return $donor;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getReceiptId() {
        // First, try to get the minimum receipt ID
        $sql = "SELECT IFNULL(MIN(id), 0) AS receipt_id 
                FROM receipt 
                WHERE book_id = :book_id AND status = 0 
                LIMIT 1";

        $this->db->query($sql, ['book_id' => 1]);
        $receipt = $this->db->fetchObject();

        if ($receipt !== null && (int) $receipt->receipt_id > 0) {
            return (int) $receipt->receipt_id;
        }
        $newReceiptId = $this->insertNewReceipt();
        return $newReceiptId;
    }

    private function insertNewReceipt() {
        $sql = "INSERT INTO receipt (book_id, issued_to, number, issued_date, created_date, created_by, modified_date, modified_by) 
                VALUES (:book_id, :issued_to, :number, :issued_date, :created_date, :created_by, :modified_date, :modified_by)";

        $this->db->query($sql, [
            'book_id' => 1,
            'issued_to' => 1,
            'number' => (int) $this->getMaxLeafNumber(1) + 1,
            'issued_date' => date("Y-m-d H:i:s"),
            'created_date' => date("Y-m-d H:i:s"),
            'created_by' => 1,
            'modified_date' => date("Y-m-d H:i:s"),
            'modified_by' => 1
        ]);

        return $this->db->lastInsertId();
    }

    private function getMaxLeafNumber($id) {
        $sql = "SELECT IFNULL(MAX(CONVERT(number, SIGNED INTEGER)), 0) AS max 
                FROM receipt 
                WHERE book_id = :book_id 
                LIMIT 1";

        $this->db->query($sql, ['book_id' => $id]);
        $receipt = $this->db->fetchObject();

        if ($receipt !== null) {
            return $receipt->max;
        }
    }

    public function addDonation($object) {
        $children = $object->children;
        unset($object->children);
        $object->parent_id = 0;

        // Start a transaction
        try {
            $this->db->beginTransaction();

            // Insert the main donation
            $sql = "INSERT INTO donations (amount,donor_id, non_eligible_amount, eligible_amount, sum_of_string, project_id, parent_id, created_date, created_by, receipt_id, receipt_date
                ,deposit_type,batch_id, status,cheque_trans_no,address1, address2, city_id, state_id, country_id, postal_code, email, home_phone, is_online) 
                    VALUES (:amount,:donor_id, :non_eligible_amount, :eligible_amount, :sum_of_string, :project_id, :parent_id, :created_date, :created_by, :receipt_id,:receipt_date,
                    :deposit_type,:batch_id, :status,:cheque_trans_no,:address1, :address2, :city_id, :state_id, :country_id, :postal_code, :email, :home_phone, :is_online)";
            $param = [
                'amount' => $object->amount,
                'donor_id' => $object->donor_id,
                'non_eligible_amount' => $object->non_eligible_amount,
                'eligible_amount' => $object->eligible_amount,
                'sum_of_string' => $object->sum_of_string,
                'project_id' => 0,
                'parent_id' => $object->parent_id,
                'created_date' => $object->created_date,
                'created_by' => $object->created_by,
                'receipt_id' => $object->receipt_id,
                'receipt_date' => $object->receipt_date,
                'deposit_type' => $object->deposit_type,
                'batch_id' => $object->batch_id,
                'status' => $object->status,
                'cheque_trans_no' => $object->cheque_trans_no,
                'address1' => $object->address1,
                'address2' => $object->address2,
                'city_id' => $object->city_id,
                'state_id' => $object->state_id,
                'country_id' => $object->country_id,
                'postal_code' => $object->postal_code,
                'email' => $object->email,
                'home_phone' => $object->home_phone,
                'is_online' => $object->is_online
            ];
            $this->db->query($sql, $param);
            $donation_id = $object->parent_id = $this->db->lastInsertId();

            // Insert each child donation
            foreach ($children as $item) {
                $child = (object) $item;
                $sqlChild = "INSERT INTO donations (amount,donor_id, non_eligible_amount, eligible_amount, sum_of_string, project_id, parent_id, created_date, created_by, receipt_id, receipt_date
                ,deposit_type,batch_id, status,cheque_trans_no,address1, address2, city_id, state_id, country_id, postal_code, email, home_phone, is_online) 
                    VALUES (:amount,:donor_id, :non_eligible_amount, :eligible_amount, :sum_of_string, :project_id, :parent_id, :created_date, :created_by, :receipt_id,:receipt_date,
                    :deposit_type,:batch_id, :status,:cheque_trans_no,:address1, :address2, :city_id, :state_id, :country_id, :postal_code, :email, :home_phone, :is_online)";

                $this->db->query($sqlChild,
                        [
                            'amount' => $child->amount,
                            'donor_id' => $object->donor_id,
                            'non_eligible_amount' => $child->non_eligible_amount,
                            'eligible_amount' => $child->eligible_amount,
                            'sum_of_string' => $child->sum_of_string,
                            'project_id' => $child->project_id,
                            'parent_id' => $object->parent_id,
                            'created_date' => $object->created_date,
                            'created_by' => $object->created_by,
                            'receipt_id' => $object->receipt_id,
                            'receipt_date' => $object->receipt_date,
                            'deposit_type' => $object->deposit_type,
                            'batch_id' => $object->batch_id,
                            'status' => $object->status,
                            'cheque_trans_no' => $object->cheque_trans_no,
                            'address1' => $object->address1,
                            'address2' => $object->address2,
                            'city_id' => $object->city_id,
                            'state_id' => $object->state_id,
                            'country_id' => $object->country_id,
                            'postal_code' => $object->postal_code,
                            'email' => $object->email,
                            'home_phone' => $object->home_phone,
                            'is_online' => $object->is_online
                        ]
                );
            }

            // Update the receipt status
            $sqlUpdateReceipt = "UPDATE receipt 
                                 SET status = 1, modified_date = :modified_date, modified_by = :modified_by 
                                 WHERE id = :receipt_id AND status = 0";

            $this->db->query($sqlUpdateReceipt, [
                'modified_date' => $object->created_date,
                'modified_by' => $object->created_by,
                'receipt_id' => $object->receipt_id
            ]);

            // Call the method to insert donation match
            $this->insertDonationMatch($object->parent_id);

            // Commit the transaction
            $this->db->commit();
            return $donation_id;
        } catch (PDOException $e) {
            // Rollback the transaction in case of an error
            $this->db->rollBack();
            throw $e; // Re-throw the exception for handling upstream
        }
    }

    public function insertDonationMatch($donation_id) {
        // Prepare the SQL statement to select matching pledges
        $sql = "SELECT p.*, d.id AS donation_id 
                FROM pledges p
                JOIN donations d ON d.donor_id = p.donor_id 
                AND d.project_id = p.project_id 
                AND d.receipt_date BETWEEN p.pledge_date AND p.due_date 
                WHERE d.parent_id = :donation_id";

        $this->db->query($sql, ['donation_id' => $donation_id]);
        $items = $this->db->fetchAllObjects();

        // Check if there are any matching items
        if (count($items) > 0) {
            foreach ($items as $item) {
                // Delete existing match logs for the donation
                $this->deleteDonationMatchLog($item->donation_id);

                // Prepare the match object
                $matchObj = [
                    "donation_id" => $item->donation_id,
                    "type" => $item->type,
                    "pledge_id" => $item->id,
                    "matched_by" => '1', // Assuming '1' is the ID of the user who matched
                    "matched_date" => date('Y-m-d H:i:s')
                ];

                // Insert the new match log
                $this->insertDonationMatchLog($matchObj);
            }
        }
    }

    private function deleteDonationMatchLog($donation_id) {
        $sql = "DELETE FROM donations_match_logs WHERE donation_id = :donation_id";
        $this->db->query($sql, ['donation_id' => $donation_id]);
    }

    private function insertDonationMatchLog($matchObj) {
        $sql = "INSERT INTO donations_match_logs (donation_id, type, pledge_id, matched_by, matched_date) 
                VALUES (:donation_id, :type, :pledge_id, :matched_by, :matched_date)";

        $this->db->query($sql, $matchObj);
    }

    public function insertApiToken($token, $expiresAt) {
        $sql = "INSERT INTO api_tokens (token, expires_at) 
            VALUES (:token, :expires_at) 
            ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at";
        $params = [
            'token' => $token,
            'expires_at' => $expiresAt
        ];
        $this->db->query($sql, $params);
    }

    public function getActiveApiToken() {
        $sql = "SELECT token 
            FROM api_tokens 
            WHERE expires_at > NOW() 
            LIMIT 1";

        $this->db->query($sql);
        $result = $this->db->fetchObject();

        if ($result) {
            return $result->token;
        } else {
            $this->deleteExpiredTokens();
            return null;
        }
    }

    private function deleteExpiredTokens() {
        $sql = "DELETE FROM api_tokens 
            WHERE expires_at <= NOW()";

        // Execute the query to delete expired tokens
        $this->db->query($sql);
    }

    public function saveCardData($data) {
        $sql = "INSERT INTO donor_tokens (
                donor_id, token, card_holder_name, token_name, 
                brand, expiry_month, expiry_year, source_donation, 
                created_date, created_by, modified_date, modified_by, status, object
            ) VALUES (
                :donor_id, :token, :card_holder_name, :token_name, 
                :brand, :expiry_month, :expiry_year, :source_donation, 
                :created_date, :created_by, :modified_date, :modified_by, :status, :object
            )";

        // Prepare the parameters for the query
        $params = [
            'donor_id' => $data['donor_id'],
            'token' => $data['id'],
            'card_holder_name' => $data['card_holder_name'],
            'token_name' => $data['card']['masked_number_last4'],
            'brand' => $data['card']['brand'], // Card brand from the response
            'expiry_month' => str_pad($data['card']['expiry_month'], 2, "0", STR_PAD_LEFT),
            'expiry_year' => $data['card']['expiry_year'], // Expiry year from the response
            'source_donation' => $data['donation_id'],
            'created_date' => date('Y-m-d H:i:s'), // Current timestamp
            'created_by' => 1,
            'modified_date' => date('Y-m-d H:i:s'), // Current timestamp
            'modified_by' => 1,
            'status' => 1, // Assuming status is active (1)
            'object' => gzencode(json_encode($data),9) // Store the full object as a JSON string
        ];

        // Execute the query
        $this->db->query($sql, $params);
        $token_id = $this->db->lastInsertId();
        $data['token_id'] = $token_id;
        $this->saveRecurringDonation($data);
    }

    private function saveRecurringDonation($data) {
        $sql = "INSERT INTO recurring_donations (
                donor_id,token_id, eligible_amount, non_eligible_amount, project_id, comments, 
                frequency, created_date, created_by, modified_date, 
                modified_by, source_donation, status, last_run
            ) VALUES (
                :donor_id, :token_id, :eligible_amount, :non_eligible_amount, :project_id, :comments, 
                :frequency, :created_date, :created_by, :modified_date, 
                :modified_by, :source_donation, :status, :last_run
            )";

        // Prepare the parameters for the query
        $params = [
            'donor_id' => $data['donor_id'],
            'token_id' => $data['token_id'],
            'eligible_amount' => $data['eligible_amount'],
            'non_eligible_amount' => $data['non_eligible_amount'],
            'project_id' => $data['project_id'],
            'comments' => $data['message'],
            'frequency' => $data['frequency'],
            'created_date' => date('Y-m-d H:i:s'), // Current timestamp
            'created_by' => 1,
            'modified_date' => date('Y-m-d H:i:s'), // Current timestamp
            'modified_by' => 1,
            'source_donation' => $data['donation_id'],
            'status' => 1,
            'last_run' => null
        ];

        $this->db->query($sql, $params);
    }

    public function getMaxDonorId() {
        $sql = "SELECT IFNULL(MAX(CONVERT(refrence_id, SIGNED INTEGER)), 0) AS lastId FROM donors";
        $this->db->query($sql);
        $result = $this->db->fetchObject();
        return $result ? (int) $result->lastId + 1 : 1; // Increment the max ID or return 1 if none exists
    }
}

?>