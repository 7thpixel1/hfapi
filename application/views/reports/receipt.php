<?php
    $border = "border: 1px solid #333;";
    $small = "font-size: 9px;";
    $unitApartment = (empty($donation->address2))? "" : lang('address2')." ". $donation->address2;
    
?>
<table width="208mm" style="padding-left: 10px" cellspacing="0" border="0" >
    <tr>
        <td>
            <table width="194mm" cellpadding="0" cellspacing="0" border="0" >

                <tr>
                    <td style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 10px; color:#333; height: 25px; line-height: 25px; text-align: center;"><?php echo lang('long_address'); ?></td>
                </tr>
                <tr>
                    <td style="height: 1px;line-height:1px; border-bottom: 1px solid #ccc;"></td>
                </tr>
                <tr>
                    <td style="height: 10px;line-height:10px;"></td>
                </tr>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td style="width:120mm;font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 11px; color:#333;"><b><?php echo lang('received_from');?>:</b><br>
<span style="width:120mm;font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 13px; color:#333;"><?php Pixel::echoString($donor->first_name)?> <?php Pixel::echoString($donor->middle_name)?> <?php Pixel::echoString($donor->last_name)?><br><br></span>
                                <b><?php echo lang('address'); ?>:</b><br><?php Pixel::echoString($donation->address1)?> <?php echo $unitApartment?><br><?php Pixel::echoString($donation->city_name)?> <?php Pixel::echoString($donation->province)?> <?php Pixel::echoString($donation->postal_code)?><br><?php Pixel::echoString($donation->country)?><br><br><b><?php echo lang('tel');?>:</b> <?php Pixel::echoString($donation->home_phone)?><br><br><b><?php echo lang('email');?>:</b><?php Pixel::echoString($donation->email)?><br>
                                </td>
                                <td style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 11px; color:#333;">
                                    <table cellpadding="3">
                                        <tr>
                                            <td style="width: 30mm;"><b><?php echo lang('date');?></b>:</td>
                                            <td><?php echo Pixel::formatDate($donation->receipt_date, "SHORT"); ?></td>
                                        </tr>
                                        <tr>
                                            <td><b style="font-size: 12px;"><?php echo lang('receipt_no');?>.</b>:</td>
                                            <td><b style="font-size: 12px;"><?php Pixel::echoString($receipt->number)?></b></td>
                                        </tr>
                                        <tr>
                                            <td><b style="font-size: 12px;"><?php echo lang('amount');?>:</b></td>
                                            <td><b style="font-size: 12px;"><?php echo Pixel::formatCurrency($donation->eligible_amount)?></b></td>
                                        </tr>
                                        <tr>
                                            <td><b><?php echo lang('sum_of');?></b>:</td>
                                            <td><?php Pixel::echoString(ucfirst(strtolower($donation->sum_of_string)))?> Dollars Only</td>
                                        </tr>
                                        <tr>
                                            <td><b><?php echo lang('payment_method');?></b>:</td>
                                            <td><?php echo Constents::getPaymentTypeById($donation->deposit_type); ?></td>
                                        </tr>
                                        <tr>
                                            <td><b><?php echo lang('transaction_id');?></b>:</td>
                                            <td><?php Pixel::echoString($donation->cheque_trans_no)?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php
                            $commonCss = "font-family:'Arial', Sans-Serif; font-size: 11px; line-height:20px; height:20px;";
                            $smallCss = "font-family:'Arial', Sans-Serif; font-size: 9px; color:#666;";
                            $headCss = $commonCss . "background-color:#153a93; color:#fff; font-weight: bold;";
                            $footCss = $commonCss . "color:#000000; font-weight: bold; background-color:#cccccc;";
                            $bodyCss = $commonCss . "color:#333333; font-weight: normal; border-bottom:1px solid #cccccc;";
                        ?>
                        <table>
                            <thead>
                            <tr>
                                <th style="<?php echo $headCss;?> width:72mm;">&nbsp; <?php echo lang('project_name');?> &nbsp;</th>
                                <th style="<?php echo $headCss;?> width:40mm; text-align: right;">&nbsp; <?php echo lang('net_amount');?> &nbsp;</th>
                                <th style="<?php echo $headCss;?> width:40mm; text-align: right;">&nbsp; <?php echo lang('non_eligible_amount');?> &nbsp;</th>
                                <th style="<?php echo $headCss;?> width:40mm; text-align: right;">&nbsp; <?php echo lang('eligible_amount');?> &nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($children as $child): ?>
                            <tr>
                                <td style="<?php echo $bodyCss;?> width:72mm;"><?php Pixel::echoString($child->name)?></td>
                                <td style="<?php echo $bodyCss;?> width:40mm; text-align: right;"><?php echo Pixel::formatCurrency($child->amount)?></td>
                                <td style="<?php echo $bodyCss;?> width:40mm; text-align: right;"><?php echo Pixel::formatCurrency($child->non_eligible_amount)?></td>
                                <td style="<?php echo $bodyCss;?> width:40mm; text-align: right;">&nbsp; <?php echo Pixel::formatCurrency($child->eligible_amount)?> &nbsp;</td>
                            </tr>
                            <?php endforeach;?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td style="<?php echo $bodyCss;?> width:72mm; text-align: right; font-weight:bold;"><?php echo lang('total')?></td>
                                <td style="<?php echo $bodyCss;?> width:40mm; text-align: right; font-weight:bold;"><?php echo Pixel::formatCurrency($donation->amount)?></td>
                                <td style="<?php echo $bodyCss;?> width:40mm; text-align: right; font-weight:bold;"><?php echo Pixel::formatCurrency($donation->non_eligible_amount)?></td>
                                <td style="<?php echo $bodyCss;?> width:40mm; text-align: right; font-weight:bold;">&nbsp; <?php echo Pixel::formatCurrency($donation->eligible_amount)?> &nbsp;</td>
                                </tr>
                            </tfoot>
                                
                            
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="height: 7px;"></td>
                </tr>
                <tr>
                    <td style="<?php echo $commonCss;?>"><?php echo lang('comments')?>:<br><?php Pixel::echoString($donation->comments)?></td>
                </tr>
                <!--
                <tr>
                    <td style="height: 7px;"></td>
                </tr>
                <tr>
                    <td style="<?php echo $commonCss;?>"><?php Pixel::echoString($receipt->first_name)?> <?php Pixel::echoString($receipt->last_name)?></td>
                </tr>
                <tr>
                    <td style="<?php echo $commonCss;?>"><?php echo lang('auth_signs'); ?></td>
                </tr>
                -->
                <tr>
                    <td style="height: 1px;line-height:1px; border-bottom: 1px solid #eee;"></td>
                </tr>
                <tr>
                    <td style="height: 10px;"></td>
                </tr>
                
            </table>
            
        </td>
    </tr>
</table>
