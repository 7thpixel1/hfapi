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
            .b-top {

                border-top: 1px solid #ccc; /* Border to differentiate table sections */
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
                    <table width="194mm" cellpadding="0" cellspacing="0" border="0" >
                        <tr>
                            <td>
                                <?php if (@count($list) > 0): ?>
                                    <table>
                                        <tr>
                                            <td style="font-family:'Open Sans Condensed',Arial, Sans-Serif; text-align: left; width: 60%">
                                                <img src="./assets/images/report-logo.png" alt="Logo">
                                            </td>
                                            <td style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 28px; color:#000; text-align: right;">
                                                Annual statement<br>Year <?php echo $year; ?>
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
                                            <td colspan="2" style="font-family:'Arial', Sans-Serif; font-size: 15px; height: 25px; line-height: 25px; color:#153a93; text-align: center; border-bottom: 1px solid #ccc;">DUPLICATE: This receipt replaces all individual receipts issued during the year</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="line-height: 5px; height: 5px;"></td>
                                        </tr>
                                        <tr>
                                            <td style="font-family:'Arial', Sans-Serif; font-size: 12px; color:#333333; width: 65mm;">
                                                <b>Name:</b> <?php echo ($donorObject->first_name . " " . $donorObject->last_name); ?><br><br>
                                                <b>Address:</b><br><?php echo ($donorObject->address1); ?><br>
                                                <?php echo $donorObject->city; ?> <?php echo $donorObject->state; ?> <?php echo ($donorObject->postal_code); ?><br>Canada<br>
                                                Date of Issue: <?php echo App\Config\Pixel::formatDate(NULL, 'SHORT') ?><br>
                                                <b>Year: </b> <?php echo $year; ?>

                                            </td>
                                            <td style="font-family:'Arial', Sans-Serif; font-size: 12px; color:#333333; width: 65mm;">
                                                <b>Donor ID:</b> <?php echo ($donorObject->refrence_id); ?><br><br>
                                                <b>Tel:</b> <?php echo ($donorObject->home_phone); ?><br><br>
                                                <b>Email:</b> <?php echo ($donorObject->username); ?><br>
                                            </td>
                                        </tr>
                                    </table>
                                    <table cellpadding="0" cellspacing="0" border="0">
                                        <?php
                                        $commonCss = "font-family:'Arial', Sans-Serif; font-size: 11px; line-height:30px; height:30px;";
                                        $headCss = $commonCss . "color:#153a93; font-weight: bold; border-top:1px solid #cccccc;";
                                        $footCss = $commonCss . "color:#000000; font-weight: bold; border-top:1px solid #cccccc; border-bottom:1px solid #cccccc;";
                                        $bodyCss = $commonCss . "color:#333333; font-weight: normal;";
                                        ?>
                                        <thead>
                                            <tr>
                                                <th class="text-left">Receipt No.</th>
                                                <th class="text-left">Project</th>
                                                <th class="text-left">Payment Method</th>
                                                <th class="text-center">Donation Date</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td colspan="5" style="height: 1px;line-height:1px; border-bottom: 1px solid #cccccc;"></td>
                                            </tr>
                                            <?php
                                            $totalEAmount = 0;
                                            foreach ($list as $row):
                                                $totalEAmount += round((float) $row->eligible_amount, 2);
                                                $projectName = trim($row->project_name);
                                                if (!empty($projectName)) {
                                                    $projects = explode(",", $row->project_name);
                                                    $projectName = (count($projects) > 1) ? $projects[0] . " + Other Projects" : $row->project_name;
                                                }
                                                ?>
                                                <tr>
                                                    <td  class="text-left"><?php echo $row->number; ?></td>
                                                    <td  class="text-left"><?php echo ($projectName); ?></td>
                                                    <td  class="text-left"><?php echo App\Config\Pixel::getPaymentTypeById($row->deposit_type); ?></td>
                                                    <td  class="text-center"><?php echo App\Config\Pixel::formatDate($row->receipt_date, "SHORT"); ?></td>
                                                    <td class="text-right"><?php echo App\Config\Pixel::formatCurrency((float) $row->eligible_amount, 2); ?></td>
                                                </tr>

                                                <?php
                                            endforeach;
                                            ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-left b-top">Total</td>
                                                <td class="text-left b-top"></td>
                                                <td class="text-left b-top"></td>
                                                <td class="text-left b-top"></td>
                                                <td class="text-right b-top"><?php echo App\Config\Pixel::formatCurrency($totalEAmount); ?></td>
                                            </tr>
                                        </tfoot>

                                    </table>



                                <?php else: ?>
                                    <b>No Record Found!</b>
                                <?php endif; ?>


                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
