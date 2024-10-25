<table width="208mm" style="padding-left: 10px" cellspacing="0" border="0" >
    <tr>
        <td>
            <table width="194mm" cellpadding="0" cellspacing="0" border="0" >
                <tr>
                    <td><?php if (@count((array) $list) > 0): ?><?php $i = 1;
    foreach ($list as $year): ?><table>
                            <tr>
                    <td colspan="3" style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 10px; color:#333; height: 25px; line-height: 25px; text-align: center; border-bottom: 1px solid #ccc;"><?php echo lang('long_address'); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="font-family:'Arial', Sans-Serif; font-size: 15px; height: 25px; line-height: 25px; color:#153a93; text-align: center; border-bottom: 1px solid #ccc;">DUPLICATE: This receipt replaces all individual receipts issued during the year</td>
                </tr>
                            <tr>
                                <td colspan="3" style="line-height: 5px; height: 5px;"></td>
                            </tr>
                            <tr>
                                <td style="font-family:'Arial', Sans-Serif; font-size: 12px; color:#333333; width: 65mm;">
<b>Name:</b> <?php Pixel::echoString($donorObject->first_name . " " . $donorObject->last_name); ?><br><br>
<b>Address:</b><br><?php Pixel::echoString($donorObject->address1); ?><br>
<?php Pixel::echoString($donorObject->city_name); ?> <?php Pixel::echoString($donorObject->province); ?> <?php Pixel::echoString($donorObject->postal_code); ?><br><?php Pixel::echoString($donorObject->country); ?><br>
                                    
                                </td>
                                <td style="font-family:'Arial', Sans-Serif; font-size: 12px; color:#333333; width: 65mm;">
                                    <b>Donor ID:</b> <?php Pixel::echoString($donorObject->refrence_id); ?><br><br>
                                    <b>Tel:</b> <?php Pixel::echoString($donorObject->home_phone); ?><br><br>
                                    <b>Email:</b> <?php Pixel::echoString($donorObject->email); ?><br>
                                </td>
                                <td style="font-family:'Arial', Sans-Serif; font-size: 12px; color:#333333; text-align:right;">
                                    <span style="font-family:'Arial', Sans-Serif; font-size: 22px; font-weight: bold; color:#153a93;"><b>Year: </b> <?php echo reset($year)->year ?></span><br><br style="line-height: 10px;">
                                    Date of Issue: <?php echo Pixel::formatDate(NULL, 'SHORT') ?><br>
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
                                            <th style="<?php echo $headCss; ?> width:20mm;"><?php echo lang('receipt_no'); ?></th>
                                            <th style="<?php echo $headCss; ?>  text-align:center; width:33mm;"><?php echo lang('donation_date'); ?></th>
                                            <th style="<?php echo $headCss; ?> width:70mm;"><?php echo lang('project_name'); ?></th>
                                            <th style="<?php echo $headCss; ?> width:30mm;"><?php echo lang('payment_method'); ?></th>
                                            <th style="<?php echo $headCss; ?>  text-align:right; width:40mm;"><?php echo lang('amount'); ?></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td colspan="7" style="height: 1px;line-height:1px; border-bottom: 1px solid #cccccc;"></td>
                                        </tr>
                                        <?php
                                        $totalEAmount = 0;
                                        foreach ($year as $row):
                                            $totalEAmount += round((float) $row->eligible_amount, 2);
                                            $projectName = trim((string)$row->project_name);
                                            if(!empty($projectName)){
                                                $projects = explode(",", $row->project_name);
                                                $projectName = (@count((array) $projects) > 1) ? $projects[0]." + Other Projects":$row->project_name;
                                            }
                                            ?>
                                            <tr>
                                                <td style="<?php echo $bodyCss; ?> width:20mm;"><?php echo $row->number; ?></td>
                                                <td style="<?php echo $bodyCss; ?>  text-align:center; width:33mm;"><?php echo Pixel::formatDate($row->receipt_date, "SHORT"); ?></td>
                                                <td style="<?php echo $bodyCss; ?> width:70mm;"><?php echo Pixel::string($projectName); ?></td>
                                                <td style="<?php echo $bodyCss; ?> width:30mm;"><?php echo Constents::getPaymentTypeById($row->deposit_type); ?></td>
                                                <td style="<?php echo $bodyCss; ?>  text-align:right; width:40mm;"><?php echo Pixel::formatCurrency((float) $row->eligible_amount, 2); ?></td>
                                            </tr>

                                            <?php
                                        endforeach;
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td style="<?php echo $footCss; ?> width:20mm;"><?php echo lang('total'); ?></td>
                                            <td style="<?php echo $footCss; ?>  text-align:center; width:33mm;"></td>
                                            <td style="<?php echo $footCss; ?>  width:70mm;"></td>
                                            <td style="<?php echo $footCss; ?>  width:30mm;"></td>
                                            <td style="<?php echo $footCss; ?>  text-align:right; width:40mm;"><?php echo Pixel::formatCurrency($totalEAmount); ?></td>
                                        </tr>
                                    </tfoot>

                                </table>
                                <?php if ($i < @count((array) $list)): ?>
                                    <br pagebreak="true" />

                                    <?php
                                endif;
                                $i++;
                            endforeach;
                            ?>

                        <?php else: ?>
                            <b>No Record Found!</b>
                        <?php endif; ?>


                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
