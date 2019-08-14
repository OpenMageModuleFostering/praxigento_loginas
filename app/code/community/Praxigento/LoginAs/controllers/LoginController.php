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
 * User: Alex Gusev <flancer64@gmail.com>
 * Date: 2/20/13
 * Time: 2:04 PM
 */
class Praxigento_LoginAs_LoginController extends Mage_Core_Controller_Front_Action
{

    protected function _isAllowed()
    {
        $result = Praxigento_LoginAs_Config::canAccessLoginAs();
        return $result;
    }

    public function asAction()
    {
        /** event logger */
        $log = Praxigento_LoginAs_Model_Logger::getLogger($this);
        /** get filename from the request parameters */
        $filename = $this->getRequest()->getPost(Praxigento_LoginAs_Config::REQ_PARAM_LAS_ID);
        /** @var $authPack Praxigento_LoginAs_Model_Package */
        $authPack = Mage::getSingleton('prxgt_lgas_model/package');
        $authPack->loadFromFile($filename);
        /** extract working data */
        $customerId = $authPack->getCustomerId();
        if (!is_null($customerId)) {
            $customerName = $authPack->getCustomerName();
            $operatorName = $authPack->getAdminName();
            $operatorIp = $authPack->getIp();
            $log->trace("Operator '$operatorName' trying to login as '$customerName' (id=$customerId) from ip '$operatorIp'...");
            /** validate current customer's session or  establish new session and validate request */
            $session = Mage::getSingleton('customer/session');
            $sessionCustomer = $session->getCustomer();
            $sessionCustomerId = $sessionCustomer->getId();
            /** this operator is already logged in as required customer */
            if (($session->isLoggedIn()) && ($customerId == $sessionCustomerId)) {
                $log->debug("Session for customer '$customerName' (id=$customerId) is already exist. Refreshing page...");
                /** save operator's name into session to use in orders later */
                $session->setData(Praxigento_LoginAs_Config::SESS_LOGGED_AS_OPERATOR, $operatorName);
            } else {
                /** establish new customer session */
                $validatorData = $session->getValidatorData();
                if ($this->getRequest()->isPost() && ($operatorIp == $validatorData['remote_addr'])) {
                    try {
                        /** @var $customer Mage_Customer_Model_Customer */
                        $customer = Mage::getModel('customer/customer')->load($customerId);
                        if ($customer->getId() == $customerId) {
                            /** check allowed websites */
                            $wsId = Mage::app()->getStore()->getWebsiteId();
                            $wsids = $customer->getSharedWebsiteIds();
                            $custWsId = $customer->getData('website_id');
                            if (
                                (in_array($wsId, $wsids)) ||
                                ($wsId == $custWsId)
                            ) {
                                $session->loginById($customerId);
                                /** save operator's name into session to use in orders later */
                                $session->setData(Praxigento_LoginAs_Config::SESS_LOGGED_AS_OPERATOR, $operatorName);
                                $log->info("New session for customer '$customerName' (id=$customerId) is established for operator '$operatorName'");
                            } else {
                                $msg = "Customer '$customerName' (id=$customerId) has no rights to access current website.";
                                $log->error($msg);
                                Mage::throwException($msg);
                            }
                        } else {
                            $msg = "Customer with id '$customerId' does not exists.";
                            $log->error($msg);
                            Mage::throwException($msg);
                        }
                    } catch (Mage_Core_Exception $e) {
                        switch ($e->getCode()) {
                            case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                                $message = $e->getMessage();
                                break;
                            default:
                                $message = $e->getMessage();
                        }
                        $session->addError($message);
                        $session->setId(null);
                        $log->error('Session ID is reset due to error is occurred. Exception is not logged.');
                    } catch (Exception $e) {
                        /** Mage::logException($e); // PA DSS violation: this exception log can disclose customer password */
                    }
                } else {
                    $log->warn("Authentication request failure: request type is not POST or operator's current ip (" .
                        $validatorData['remote_addr'] . ") is not equal to ip from authentication package ($operatorIp).");
                }
            }
        } else {
            $log->warn("Cannot get customer id for authentication package '$filename'.");
        }
        $this->_redirect('customer/account');
    }
}
