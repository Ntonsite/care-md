<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 3.0//EN" "html.dtd">
<HTML>
    <HEAD>
        <TITLE><?php echo $LDReportingModule; ?></TITLE>
        <meta name="Description" content="Hospital and Healthcare Integrated Information System - CARE2x">
        <meta name="Author" content="Moye Masenga">
        <meta name="Generator" content="various: Quanta, AceHTML 4 Freeware, NuSphere, PHP Coder">
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

        <script language="javascript" >

            function gethelp(x, s, x1, x2, x3, x4)
            {
                if (!x)
                    x = "";
                urlholder = "../../main/help-router.php?sid=<?php echo sid; ?>&lang=$lang&helpidx=" + x + "&src=" + s + "&x1=" + x1 + "&x2=" + x2 + "&x3=" + x3 + "&x4=" + x4;
                helpwin = window.open(urlholder, "helpwin", "width=790,height=540,menubar=no,resizable=yes,scrollbars=yes");
                window.helpwin.moveTo(0, 0);
            }


<?php require($root_path . 'include/inc_checkdate_lang.php'); ?>

        </script> 

        <script language="javascript" src="<?php echo $root_path; ?>js/setdatetime.js"></script>
        <script language="javascript" src="<?php echo $root_path; ?>js/checkdate.js"></script>
        <script language="javascript" src="<?php echo $root_path; ?>js/dtpick_care2x.js"></script>
        <script src="<?php print $root_path; ?>/include/_jquery.js" language="javascript"></script> 

        <link rel="stylesheet" href="../../css/themes/default/default.css" type="text/css">
        <script language="javascript" src="../../js/hilitebu.js"></script>

        <STYLE TYPE="text/css">
            A:link  {color: #000066;}
            A:hover {color: #cc0033;}
            A:active {color: #cc0000;}
            A:visited {color: #000066;}
            A:visited:active {color: #cc0000;}
            A:visited:hover {color: #cc0033;}
        </style>

    </HEAD>

    <BODY bgcolor=#ffffff link=#000066 alink=#cc0000 vlink=#000066  >

        <!-- START HEAD OF HTML CONTENT -->


        <!-- END HEAD OF HTML CONTENT -->

        <form name="form1" method="post" action=""></p>
        <DIV align="center">
            <h2>Doctors' Patients Attendance List</h2>
        </DIV>
        <table border="1" cellspacing="0" cellpadding="2" align="center" bgcolor=#ffffdd>
            <tr> 

            </tr>
        </table>
        <br><br><br>  <br><br><br>						  


        <!-- START BOTTIOM OF HTML CONTENT --->
        <table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#cfcfcf">
            <tr>
                <td align="center">
                    <table width="100%" bgcolor="#ffffff" cellspacing=0 cellpadding=5>
                        <tr>
                            <td>
                                <div class="copyright">
                                    <script language="JavaScript">
            <!-- Script Begin
                function openCreditsWindow() {

                urlholder = "../../language/$lang/$lang_credits.php?lang=$lang";
                creditswin = window.open(urlholder, "creditswin", "width=500,height=600,menubar=no,resizable=yes,scrollbars=yes");

            }
            //  Script End -->
                                    </script>


                                    <a href="http://www.care2x.org" target=_new>CARE2X 2nd Generation pre-deployment 2.0.2</a> :: <a href="../../legal_gnu_gpl.htm" target=_new> License</a> ::
                                    <a href=mailto:info@care2x.org>Contact</a>  :: <a href="../../language/en/en_privacy.htm" target="pp"> Our Privacy Policy </a> ::
                                    <a href="../../docs/show_legal.php?lang=$lang" target="lgl"> Legal </a> ::
                                    <a href="javascript:openCreditsWindow()"> Credits </a> ::.<br>

                                </div>
                            </td>
                        <tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- START BOTTIOM OF HTML CONTENT --->

</BODY>
