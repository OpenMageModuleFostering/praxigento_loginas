<?php
/**
 * Copyright (c) 2013, Praxigento
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and the following
 *      disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the
 *      following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * Authentication package to be send from adminhtml to the front through the file system.
 * Also used as a block data bean on UI (to compose redirection form).
 *
 * See: http://stackoverflow.com/questions/4006183/magento-passing-data-between-a-controller-and-a-block.
 * User: Alex Gusev <flancer64@gmail.com>
 */
class Praxigento_LoginAs_Model_Package extends Varien_Object
{
    const PREFIX = 'las';
    /** @var string administrator's name to be displayed in orders grid and logs */
    private $_adminName;
    private $_customerId;
    private $_customerName;
    private $_ip;
    /** @var string ID of the package */
    private $_packageId;
    /** @var string URL to use on form to redirect admin to the customer's website */
    private $_redirectUrl;

    public function getAdminName()
    {
        return $this->_adminName;
    }

    public function setAdminName($adminName)
    {
        $this->_adminName = $adminName;
    }

    public function getCustomerId()
    {
        return $this->_customerId;
    }

    public function setCustomerId($customerId)
    {
        $this->_customerId = $customerId;
    }

    public function getCustomerName()
    {
        return $this->_customerName;
    }

    public function setCustomerName($customerName)
    {
        $this->_customerName = $customerName;
    }

    public function getIp()
    {
        return $this->_ip;
    }

    public function setIp($ip)
    {
        $this->_ip = $ip;
    }

    /**
     * @return string
     */
    public function getPackageId()
    {
        return $this->_packageId;
    }

    /**
     * @param string $packageId
     */
    public function setPackageId($packageId)
    {
        $this->_packageId = $packageId;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * @param string $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->_redirectUrl = $redirectUrl;
    }

    /**
     * Loads "Login As" parameters from file and removes file from disk.
     * @param $filename
     */
    public function loadFromFile($filename)
    {
        // load data
        $this->_packageId = $filename;
        $fname            = sys_get_temp_dir() . DS . $this->_packageId;
        $data             = file($fname);
        if (is_array($data) && sizeof($data >= 4)) {
            $this->_adminName    = trim($data[0]);
            $this->_customerId   = trim($data[1]);
            $this->_customerName = trim($data[2]);
            $this->_ip           = trim($data[3]);
        }
        // remove file
        unlink($fname);
        $this->_packageId = null;
    }

    /**
     * Saves login data into file in the temporary directory and returns name of the tmp file including extension.
     * @return string
     */
    public function saveAsFile()
    {
        // generate unique filename and create file to save login data
        $fname            = tempnam(sys_get_temp_dir(), self::PREFIX);
        $pathParts        = pathinfo($fname);
        $this->_packageId = $pathParts['basename'];
        $handle           = fopen($fname, "w");
        // write login data to file
        $content = $this->_adminName . "\n";
        $content .= $this->_customerId . "\n";
        $content .= $this->_customerName . "\n";
        $content .= $this->_ip . "\n";
        fwrite($handle, $content);
        fclose($handle);
        return $this->_packageId;
    }
}
