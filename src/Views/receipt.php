<?php
$border = "border: 1px solid #333;";
$small = "font-size: 9px;";
$unitApartment = (empty($donation->address2)) ? "" : ('address2') . " " . $donation->address2;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Donation</title>
        <style>

            body{
                font-family:sans-serif;
                font-size:12px!important;
                color:#333!important;
                background: #FFF;
                margin-bottom: 0px;
                
            }
            table {
                width: 100%;
                border-collapse: collapse; /* Ensures no extra space between table cells */
            }

            th, td {
                padding: 5px; /* Control padding for uniformity */
                border: 0px; /* Border to differentiate table sections */
            }

            th {
                background-color: #153a93;
                color: #fff;
                text-align: left;
                font-weight: bold;
                font-size: 11px;
                padding: 7px; /* Ensure header padding is the same across all headers */
            }

            

            tfoot td {
                font-weight: bold;
                
            }

            .text-left {
                text-align: left;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }

        </style>
    </head>
    <body>
        <table style="padding:0px;" cellspacing="0" border="0" >
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" border="0" >

                        <tr>
                            <td style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 10px; color:#333; text-align: left; width: 60%">
                                <img src="./assets/images/report-logo.png" alt="Logo">
                            </td>
                            <td style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 28px; color:#000; text-align: right;">
                                DONATION RECEIPT<br><?php echo date('Y', strtotime($donation->receipt_date)); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 1px;line-height:1px; border-bottom: 1px solid #ccc;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 10px; color:#333; height: 30px; line-height: 30px; text-align: center;">600 Bowes Rd. Unit 40, Concord Ontario, L4K 4A3, Phone:416-440-0346 Fax:416-440-0346 Email:info@humanityfirst.ca, www.humanityfirst.ca</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 1px;line-height:1px; border-bottom: 1px solid #ccc;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 10px;line-height:10px;"></td>
                        </tr>
                        <tr>
                            <td style="width:60%;font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 11px; color:#333;"><b>Received From:</b><br>
                                <span style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 13px; color:#333;"><?php echo ($donor->first_name) ?> <?php echo ($donor->middle_name) ?> <?php echo ($donor->last_name) ?><br><br></span>
                                <b>Address:</b><br><?php echo ($donation->address1) ?> <?php echo $unitApartment ?><br>
                                <?php echo ($donation->city_name) ?> <?php echo ($donation->province) ?> <?php echo ($donation->postal_code) ?><br>
                                <?php echo ($donation->country) ?><br><br>
                                <b>Tel:</b> <?php echo ($donation->home_phone) ?><br><br><b>Email:</b><?php echo ($donation->email) ?><br>
                            </td>
                            <td style="width:40%; font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 11px; color:#333;">
                                <table cellpadding="3" style="width: 100%;">
                                    <tr>
                                        <td style="width: 50%;"><b>Date</b>:</td>
                                        <td><?php echo App\Config\Pixel::formatDate($donation->receipt_date, 'SHORT'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><b style="font-size: 12px;">Receipt No.</b>:</td>
                                        <td><b style="font-size: 12px;"><?php echo ($receipt->number) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td><b style="font-size: 12px;">Amount:</b></td>
                                        <td><b style="font-size: 12px;"><?php echo App\Config\Pixel::formatCurrency($donation->eligible_amount) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td><b>The Sum of</b>:</td>
                                        <td><?php echo (ucfirst(strtolower($donation->sum_of_string))) ?> Dollars Only</td>
                                    </tr>
                                    <tr>
                                        <td><b>Payment Method</b>:</td>
                                        <td><?php echo ($donation->deposit_type); ?></td>
                                    </tr>
                                    <tr>
                                        <td><b>Transaction Id</b>:</td>
                                        <td><?php echo ($donation->cheque_trans_no) ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 10px;line-height:10px;"></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <table cellspacing="0" cellpadding="0" border="0">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Project Name</th>
                                            <th class="text-right">Received Amount</th>
                                            <th class="text-right">Non Eligible Amount</th>
                                            <th class="text-right">Eligible Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($children as $child): ?>
                                            <tr>
                                                <td class="text-left"><?php echo ($child->name) ?></td>
                                                <td class="text-right"><?php echo App\Config\Pixel::formatCurrency($child->amount) ?></td>
                                                <td class="text-right"><?php echo App\Config\Pixel::formatCurrency($child->non_eligible_amount) ?></td>
                                                <td class="text-right">&nbsp; <?php echo App\Config\Pixel::formatCurrency($child->eligible_amount) ?> &nbsp;</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td  class="text-left">Total Amount</td>
                                            <td class="text-right"><?php echo App\Config\Pixel::formatCurrency($donation->amount) ?></td>
                                            <td class="text-right"><?php echo App\Config\Pixel::formatCurrency($donation->non_eligible_amount) ?></td>
                                            <td class="text-right">&nbsp; <?php echo App\Config\Pixel::formatCurrency($donation->eligible_amount) ?> &nbsp;</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 7px;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="<?php echo $commonCss; ?>"><?php echo ('comments') ?>:<br><?php echo ($donation->comments) ?></td>
                        </tr>

                        <tr>
                            <td colspan="2" style="height: 1px;line-height:1px; border-bottom: 1px solid #eee;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 10px;"></td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>

    </body>
</html>