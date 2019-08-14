<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

/**
 * Block prints out HTML code directly, without any templates.
 */
class Praxigento_LoginAs_Block_Adminhtml_Redirect
    extends \Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        /** @var $authPack Praxigento_LoginAs_Model_Package */
        $authPack = Mage::getSingleton('prxgt_lgas_model/package');
        $url = htmlentities($authPack->getRedirectUrl());
        $formName = Praxigento_LoginAs_Config::REQ_PARAM_LAS_ID;
        $packageId = htmlentities($authPack->getPackageId());
        /** Post file name with login parameters */
        $out = "<html>
<body>
<form action=\"$url\" method=\"post\" id=\"login-form\">
    <input type=\"hidden\" name=\"$formName\" id=\"$formName\" value=\"$packageId\"/>
</form>
<script type=\"text/javascript\">
    //<![CDATA[
    document.getElementById(\"login-form\").submit();
    //]]>
</script>
</body>
</html>";
        return $out;
    }

}