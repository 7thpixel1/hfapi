<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">

            <meta name="viewport" content="width=device-width, initial-scale=1.0">	
                <title><?php echo $heading; ?></title>
                <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel='stylesheet' type='text/css'>

                    <style type="text/css">
                        img {
                            height:auto;
                        }
                        .button {
                            display: inline-block;
                            padding: 15px 25px;
                            font-size: 18px;
                            color: #FFFFFF !important;
                            background-color: #0069b4;
                            text-decoration: none;
                            border-radius: 5px;
                            text-align: center;
                        }
                        .note {
                            margin-top: 10px;
                            font-size: 14px;
                            color: #555555;
                        }
                        @media only screen and (max-width: 550px), screen and (max-device-width: 550px) {
                            .mobile-td{
                                text-align:center!important;
                            }
                            .mobile-img{
                                margin-left:auto!important;
                                margin-right:auto!important;
                            }
                            .responsive{
                                width:100%!important;
                                float:left;
                            }
                        }
                    </style>		
                    </head>
                    <body bgcolor="#F8F8F8" border="0" cell cellspacing="0"  >

                        <!--[if (gte mso 9)|(IE)]>
                          <table width="600" align="center" cell cellspacing="0" border="0">
                                <tr>
                                  <td>
                        <![endif]-->	
                        <table bgcolor="#FFFFFF" width="100%" style="max-width:595px; margin:10px auto;" cell cellspacing="0"  ><tr><td align="center" valign="top">
                                    <table width="100%" bgcolor="#FFFFFF" cell cellspacing="0" border="0">

                                        <tr><td height="100" width="100%" bgcolor="#FFFFFF" style="padding-left:20px;padding-right:20px;" valign="middle" class="mobile-td">
                                                <a href="http://www.humanityfirstcanada.ca/" target="_blank">
                                                    <img src="<?php echo $app_url; ?>assets/images/report-logo.png" alt=""/>
                                                </a>
                                            </td></tr>
                                        <tr>
                                            <td height="3" bgcolor="#1bb4eb">

                                            </td>
                                        </tr>
                                    </table>
                                    <table width="100%" cell cellspacing="0" border="0">
                                        <tr><td height="30" width="100%" class="mobile-td" style="font-family:'Open Sans',Arial, Sans-Serif; font-size: 8pt; text-align:right; color:#777777; line-height:22px; padding-right:30px; padding-left:30px;" valign="middle">
                                                <p>Date: <?php echo date('Y-m-d H;i:s'); ?></p>
                                            </td></tr>	
                                        <tr><td height="30" width="100%" class="mobile-td" style="padding-right:30px; padding-left:30px; padding-top:20px;" valign="middle">
                                                <h1 style="font-family:'Open Sans Condensed',Arial, Sans-Serif; font-size: 22px; font-weight: 700; text-align:left; color:#666; padding: 0px;"><?php echo $heading; ?></h1>
                                            </td></tr>						
                                        <tr><td width="100%" class="mobile-td" style="color:#818285; font-family:'Open Sans',Arial, Sans-Serif; font-size: 14px; font-weight: 400;line-height:22px; text-align:left;padding-left:30px; padding-right:30px; padding-top:10px; padding-bottom:10px;" valign="middle">

                                                <?php
                                                echo "Dear " . $name . ",<br><br>";
                                                ?>
                                                <?php echo $message; ?>                    
                                            </td></tr>
                                        <tr>
                                            <td style="color:#a2a2a2; font-family:'Open Sans',Arial, Sans-Serif; font-size: 14px;  text-align:left; line-height:22px; padding:30px;">
                                                <b><br>Thank you,<br>Humanity First Canada</b><br><br><small>600 Bowes Rd. Unit 40,<br>Concord Ontario, L4K 4A3 Canada<br>Tel: +1 (416) 440-0346<br>Fax: +1 (416) 440-0346</small>
                                                                                    </td>
                                                                                    </tr>
                                                                                    </table>
                                                                                    <table width="100%" cell cellspacing="0" border="0">
                                                                                        <tr>
                                                                                            <td height="30" width="100%" style="padding-right:20px; padding-left:20px; background: #1bb4eb;" valign="middle">
                                                                                                <p style="font-family:'Open Sans',Arial, Sans-Serif; font-weight: 400; font-size: 14px; text-align:center; color:#FFFFFF; line-height:20px; display:block; margin-top:1em; margin-bottom:1em;">
                                                                                                    If you have any questions, please feel free to ask at: <a href="mailto:info@humanityfirst.ca" style="color:#FFFFFF;">info@humanityfirst.ca</a>
                                                                                                </p>
                                                                                            </td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td height="30" width="100%" bgcolor="" style="font-family:'Open Sans', Arial, Sans-Serif; font-size: 11px; text-align:center; color:#133c8c; line-height:16px; margin-top:1em; margin-bottom:1em; padding-right:20px; padding-left:20px;" valign="middle">

                                                                                                You are receiving this email because you transacted with Humanity First. Humanity First is a registered non-profit charitable organization. Humanity First is CASL-compliant. You may unsubscribe or change your email preferences
                                                                                                at any time by emailing to <a href="mailto:info@humanityfirst.ca">info@humanityfirst.ca</a>. Copyright &copy; <?php echo date('Y'); ?> Humanity First Canada.<br>
                                                                                                    <b>Humanity First Canada<br></b>
                                                                                                    600 Bowes Road, #40, Concord, Ontario, L4K 4A3 Canada<br>
                                                                                                        Tel: +1 (416) 440-0346, Email: <a href="mailto:info@humanityfirst.ca">info@humanityfirst.ca</a>, Website: <a href="http://www.humanityfirst.ca/">www.humanityfirst.ca</a>
                                                                                                        </td>
                                                                                                        </tr>	
                                                                                                        </table>						

                                                                                                        </td></tr></table>

                                                                                                        <!--[if (gte mso 9)|(IE)]>
                                                                                                                  </td>
                                                                                                                </tr>
                                                                                                        </table>
                                                                                                        <![endif]-->		

                                                                                                        </body>

                                                                                                        </html>