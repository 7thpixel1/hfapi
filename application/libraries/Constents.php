<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Description of Constents
 *
 * @author saqib
 */
class Constents {

    public static $ENABLE = 1;
    public static $DISABLE = 0;
    public static $REGEX_SAFE_NO_TAG = 'A-Za-z0-9±ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİı“”~«»–—œ™®©:,\x22\^\{\}\[\]\.\-_=;!\+@\$\*\?#%&\/\(\)\'\s'."\\\\";
    public static $REGEX_SAFE_ALPHANUMERIC = 'A-Za-z0-9±ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ:\.\-_\'\s';
    public static $REGEX_SAFE_ALPHA = 'A-Za-zÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ:\.\-_\'\s';
    public static $REGEX_SAFE_ADDRESS2 = 'A-Za-z0-9ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ:\#\/\\\(\)\.\-_\'\s';
    //public static $REGEX_SAFE_PWD = '(?=.*\d)(?!.*[\s\x22\x27`~%|&|\?\/\[\]{}<>\\\\])(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{6,16}';
    public static $REGEX_SAFE_PWD = '(?=^.{6,16}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$';
    
    public static $ACT_DONORS = 1;
    public static $ACT_DONATIONS = 2;
    public static $ACT_BATCH = 3;
    public static $ACT_RECIPT_BOOK = 4;
    public static $ACT_RECIPT = 5;
    public static $ACT_USER = 6;
    public static $ACT_PROJECT = 7;
    public static $ACT_PROGRAM = 8;
    public static $ACT_BRANCH = 9;
    public static $ACT_PLEDGE = 10;

    public static function getGenderList() {

        return array("1" => (object) array("id" => 1, "name" => "Male"),
            "2" => (object) array("id" => 2, "name" => "Female"),
            "3" => (object) array("id" => 3, "name" => "prefer not to disclose"),
        );
    }

    public static function getLangList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Other"),
            "2" => (object) array("id" => 1, "name" => "English"),
            "3" => (object) array("id" => 2, "name" => "French"),
            "4" => (object) array("id" => 3, "name" => "Spanish"),
            "5" => (object) array("id" => 3, "name" => "Wutana"),
            "6" => (object) array("id" => 3, "name" => "Yeni"),
            "7" => (object) array("id" => 3, "name" => "Jalaa"),
            "8" => (object) array("id" => 3, "name" => "Irimba"),
            "9" => (object) array("id" => 3, "name" => "Urdu"),
        );
    }

    public static function getReligionList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Ahmadiyya"),
            "2" => (object) array("id" => 2, "name" => "Islam"),
            "3" => (object) array("id" => 3, "name" => "Judaism"),
            "4" => (object) array("id" => 4, "name" => "Christianity"),
            "5" => (object) array("id" => 5, "name" => "Buddhism"),
            "6" => (object) array("id" => 6, "name" => "Hindu"),
            "7" => (object) array("id" => 7, "name" => "Other"),
            "0" => (object) array("id" => 0, "name" => "Unknown"),
        );
    }

    public static function getProjectClassList() {

        return array("1" => (object) array("id" => 1, "name" => "Charitable"),
            "2" => (object) array("id" => 2, "name" => "Non-Charitable"),
        );
    }

    public static function getStatusList() {

        return array("1" => (object) array("id" => 1, "name" => "Active"),
            "0" => (object) array("id" => 0, "name" => "In-active"),
        );
    }
    public static function getAssetsStatusList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Pending"),
            "2" => (object) array("id" => 2, "name" => "Started"),
            "3" => (object) array("id" => 3, "name" => "In-progress"),
            "4" => (object) array("id" => 4, "name" => "Completed"),
            
        );
    }

    public static function getFoodStatusList() {

        return array("1" => (object) array("id" => 1, "name" => "New"),
            "2" => (object) array("id" => 2, "name" => "Reviewed"),
            "3" => (object) array("id" => 3, "name" => "Rejected"),
            "4" => (object) array("id" => 4, "name" => "Completed"),
        );
    }

    public static function getReceiptStatusList() {

        return array("1" => (object) array("id" => 1, "name" => "Active"),
            "0" => (object) array("id" => 0, "name" => "In-active"),
            "2" => (object) array("id" => 2, "name" => "Closed"),
            "3" => (object) array("id" => 3, "name" => "Lost"),
        );
    }

    public static function getDonationStatusList() {

        return array("1" => (object) array("id" => 1, "name" => "Void"),
            "0" => (object) array("id" => 0, "name" => "Valid"),
        );
    }

    public static function getProjectRestricedList() {

        return array("1" => (object) array("id" => 1, "name" => "Restricted"),
            "2" => (object) array("id" => 0, "name" => "Unrestricted"),
        );
    }

    public static function getProjectCategoryList() {

        return array("1" => (object) array("id" => 1, "name" => "Donation Income"),
            "2" => (object) array("id" => 2, "name" => "Grants and Funding"),
            "3" => (object) array("id" => 3, "name" => "Not Applicable"),
        );
    }

    public static function getProjectTypeList() {

        return array("1" => (object) array("id" => 1, "name" => "International"),
            "2" => (object) array("id" => 2, "name" => "Canadian"),
        );
    }

    public static function getPaymentTypeList() {

        return array("1" => (object) array("id" => 1, "name" => "Cash"),
            "2" => (object) array("id" => 2, "name" => "Cheque"),
            "3" => (object) array("id" => 3, "name" => "Credit Card"),
            "4" => (object) array("id" => 4, "name" => "PayPal"),
            "5" => (object) array("id" => 5, "name" => "CanadaHelps"),
            "6" => (object) array("id" => 6, "name" => "Moneris"),
            "7" => (object) array("id" => 7, "name" => "Other"),
        );
    }

    public static function getBooleanList() {

        return array("0" => (object) array("id" => 0, "name" => "No"),
            "1" => (object) array("id" => 1, "name" => "Yes"),
        );
    }

    public static function getBatchStatusList() {

        return array("0" => (object) array("id" => 0, "name" => "Open"),
            "1" => (object) array("id" => 1, "name" => "Close"),
        );
    }

    public static function getTitlesList() {

        return array("1" => (object) array("id" => "Mr.", "name" => "Mr."),
            "2" => (object) array("id" => "Mrs.", "name" => "Mrs."),
            "3" => (object) array("id" => "Ms.", "name" => "Ms."),
            "4" => (object) array("id" => "Dr.", "name" => "Dr."),
            "5" => (object) array("id" => "Org.", "name" => "Org."),
        );
    }

    public static function getContactMethod() {

        return array("1" => (object) array("id" => 1, "name" => "Email"),
            "2" => (object) array("id" => 2, "name" => "Phone"),
            "3" => (object) array("id" => 3, "name" => "Cell")
        );
    }

    public static function getTypeList() {

        return array(
            "0" => (object) array("id" => 0, "name" => "Individual."),
            "2" => (object) array("id" => 2, "name" => "Individual"),
            "1" => (object) array("id" => 1, "name" => "AMJ"),
            "3" => (object) array("id" => 3, "name" => "Business"),
            "4" => (object) array("id" => 4, "name" => "Non-profit Organizations"),
            "5" => (object) array("id" => 5, "name" => "Government")
        );
    }

    public static function getTypeById($id) {
        try {
            $array = Constents::getTypeList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    public static function getGenderById($id) {
        try {
            $array = Constents::getGenderList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }
    public static function getLangById($id) {
        try {
            $array = Constents::getLangList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }
    public static function getReligionById($id) {
        try {
            $array = Constents::getReligionList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    public static function getPaymentTypeById($id) {
        try {
            $array = Constents::getPaymentTypeList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    public static function getStatusById($id) {
        try {
            $array = Constents::getStatusList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }
    public static function getAssetsStatusById($id) {
        try {
            $array = Constents::getAssetsStatusList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    public static function getReceiptStatusById($id) {
        try {
            $array = Constents::getReceiptStatusList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    public static function getDonationStatusById($id) {
        try {
            $array = Constents::getDonationStatusList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    public static function getBooleanById($id) {
        try {
            $array = Constents::getBooleanList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    public static function getBatchStatusById($id) {

        try {
            $array = Constents::getBatchStatusList();
            $object = $array[(int) $id];
            return ($object === NULL) ? '' : $object->name;
        } catch (Exception $e) {
            return '';
        }
    }

    /* immigration func */

    public static function getHFStatusList() {

        return array("1" => (object) array("id" => 1, "name" => "Email Received"),
            "2" => (object) array("id" => 2, "name" => "Intake Sent"),
            "3" => (object) array("id" => 3, "name" => "Intake Received"),
            "4" => (object) array("id" => 4, "name" => "In Process"),
            "5" => (object) array("id" => 5, "name" => "Forms Sent for Signature"),
            "6" => (object) array("id" => 6, "name" => "Ready for Submission"),
            "7" => (object) array("id" => 7, "name" => "Pending Submission"),
            "8" => (object) array("id" => 8, "name" => "Submitted"),
            "9" => (object) array("id" => 9, "name" => "Approved"),
            "10" => (object) array("id" => 10, "name" => "Rejected"),
            "11" => (object) array("id" => 11, "name" => "Cancelled"),
            "12" => (object) array("id" => 12, "name" => "Withdrawn"),
            "13" => (object) array("id" => 13, "name" => "Arrived"),
            "14" => (object) array("id" => 14, "name" => "On Hold"),
        );
    }

    public static function getCICStatusList() {

        return array("1" => (object) array("id" => 1, "name" => "In Process"),
            "2" => (object) array("id" => 10, "name" => "Rejected"),
            "3" => (object) array("id" => 9, "name" => "Approved"),
            "4" => (object) array("id" => 12, "name" => "Withdrawn"),
            "5" => (object) array("id" => 13, "name" => "Closed"),
            "6" => (object) array("id" => 14, "name" => "Other"),
        );
    }

    public static function getimmiTypeList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Individual"),
            "5" => (object) array("id" => 2, "name" => "Family"),
        );
    }

    public static function getImmiGenderList() {

        return array("1" => (object) array("id" => 1, "name" => "Male"),
            "2" => (object) array("id" => 2, "name" => "Female"),
        );
    }

    /* Sort By */

    public static function getSortDirList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "ASC"),
            "2" => (object) array("id" => 2, "name" => "DESC"),
        );
    }

    public static function getSortDirById($id) {
        try {
            $array = Constents::getSortDirList();
            $object = $array[(int) $id];
            return ($object === NULL) ? 'desc' : strtolower($object->name);
        } catch (Exception $e) {
            return 'desc';
        }
    }

    public static function getDonorSortList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Donor ID", "column" => "ABS(d.refrence_id)"),
            "2" => (object) array("id" => 2, "name" => "Organization", "column" => "d.branch_name"),
            "3" => (object) array("id" => 3, "name" => "Total Donations", "column" => "collection"),
            "4" => (object) array("id" => 4, "name" => "Type", "column" => "d.type"),
        );
    }

    public static function getDonorSortById($id) {
        $array = Constents::getDonorSortList();
        try {
            $object = $array[(int) $id];
            return ($object === NULL) ? $array[1]->column : $object->column;
        } catch (Exception $e) {
            return $array[1]->column;
        }
    }

    public static function getDonationSortList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Batch #", "column" => "a.batch_id"),
            "2" => (object) array("id" => 2, "name" => "Receipt #", "column" => "ABS(c.number)"),
            "3" => (object) array("id" => 3, "name" => "Amount", "column" => "a.amount"),
            "4" => (object) array("id" => 4, "name" => "Date", "column" => "a.receipt_date"),
            "5" => (object) array("id" => 5, "name" => "Donor ID", "column" => "ABS(b.refrence_id)"),
        );
    }

    public static function getDonationSortById($id) {

        $array = Constents::getDonationSortList();
        try {
            $object = $array[(int) $id];
            return ($object === NULL) ? $array[1]->column : $object->column;
        } catch (Exception $e) {
            return $array[1]->column;
        }
    }

    public static function getBatchSortList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Batch #", "column" => "b.batch_number"),
            "2" => (object) array("id" => 2, "name" => "Date", "column" => "b.batch_date"),
            "3" => (object) array("id" => 3, "name" => "Amount", "column" => "b.amount"),
            "4" => (object) array("id" => 4, "name" => "Balance", "column" => "balance"),
        );
    }

    public static function getBatchSortById($id) {

        $array = Constents::getBatchSortList();
        try {
            $object = $array[(int) $id];
            return ($object === NULL) ? $array[1]->column : $object->column;
        } catch (Exception $e) {
            return $array[1]->column;
        }
    }

    public static function getReceiptSortList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Book #", "column" => "b.book_number"),
            "2" => (object) array("id" => 2, "name" => "Branch", "column" => "branch_name"),
            "3" => (object) array("id" => 3, "name" => "Issue To", "column" => "u.first_name"),
            "4" => (object) array("id" => 4, "name" => "Total Collection", "column" => "collection"),
        );
    }

    public static function getReceiptSortById($id) {

        $array = Constents::getReceiptSortList();
        try {
            $object = $array[(int) $id];
            return ($object === NULL) ? $array[1]->column : $object->column;
        } catch (Exception $e) {
            return $array[1]->column;
        }
    }

    public static function getPledgeSortList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "ID", "column" => "a.id"),
            "2" => (object) array("id" => 2, "name" => "Amount", "column" => "a.amount"),
            "3" => (object) array("id" => 3, "name" => "Date", "column" => "a.due_date"),
            "4" => (object) array("id" => 4, "name" => "Donor ID", "column" => "ABS(b.refrence_id)"),
        );
    }

    public static function getPledgeDateList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Due Date", "column" => "a.due_date"),
            "2" => (object) array("id" => 2, "name" => "Pledge Date", "column" => "a.pledge_date"),
        );
    }

    public static function getPledgeSortById($id) {

        $array = Constents::getPledgeSortList();
        try {
            $object = $array[(int) $id];
            return ($object === NULL) ? $array[1]->column : $object->column;
        } catch (Exception $e) {
            return $array[1]->column;
        }
    }

    public static function getPledgeDateById($id) {

        $array = Constents::getPledgeDateList();
        try {
            $object = $array[(int) $id];
            return ($object === NULL) ? $array[1]->column : $object->column;
        } catch (Exception $e) {
            return $array[1]->column;
        }
    }
    public static function getAssetDateById($id) {

        $array = Constents::getAssetDateList();
        try {
            $object = $array[(int) $id];
            return ($object === NULL) ? $array[1]->column : $object->column;
        } catch (Exception $e) {
            return $array[1]->column;
        }
    }
    public static function getAssetDateList() {

        return array(
            "1" => (object) array("id" => 1, "name" => "Start Date", "column" => "a.start_date"),
            "2" => (object) array("id" => 2, "name" => "End Date", "column" => "a.end_date"),
        );
    }

}

?>
