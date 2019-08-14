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
 * Controller to process operator's requests to redirect to the frontend to login as a customer.
 */
class Praxigento_LoginAs_RedirectController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        $result = Praxigento_LoginAs_Config::canAccessLoginAs();
        return $result;
    }

    public function indexAction()
    {
        if (Praxigento_LoginAs_Config::cfgGeneralEnabled()) {
            /** define operator name */
            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('admin/session');
            if ($session->isLoggedIn()) {
                /** @var $user Mage_Admin_Model_User */
                $user = $session->getUser();
                $operator = $user->getName() . ' (' . $user->getEmail() . ')';
                /** if there is customer data in request */
                if (!is_null($this->getRequest()->getParams())) {
                    $params = $this->getRequest()->getParams();
                    if (!is_null($params[Praxigento_LoginAs_Config::REQ_PARAM_LAS_ID])) {
                        /** extract customer ID from request and load customer data */
                        $customerId = $params[Praxigento_LoginAs_Config::REQ_PARAM_LAS_ID];
                        /** @var $customer Mage_Customer_Model_Customer */
                        $customer = Mage::getModel('customer/customer')->load($customerId);
                        if ($customer->getId() == $customerId) {
                            $customerName = $customer->getName();
                            /** define URL to login to customer's website */
                            $wsId = $customer->getData('website_id');
                            if (is_null($wsId)) {
                                $wsId = Mage::app()->getStore()->getWebsiteId();
                            }
                            /** @var $website Mage_Core_Model_Website */
                            $website = Mage::getModel('core/website')->load($wsId);
                            $defStoreId = $website->getDefaultStore()->getId();
                            $baseTarget = Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_URL, $defStoreId);
                            $baseSource = Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_URL);
                            /** compose redirection URL and replace current base by target base */
                            $urlModel = Mage::getModel('core/url');
                            $store = Mage::getModel('core/store')->load($defStoreId);
                            $urlModel->setStore($store);
                            $url = $urlModel->getUrl(
                                Praxigento_LoginAs_Config::XMLCFG_ROUTER_FRONT .
                                Praxigento_LoginAs_Config::ROUTE_CUSTOMER_LOGINAS);
                            $url = str_replace($baseSource, $baseTarget, $url);
                            /** compose authentication package */
                            /** @var $authPack Praxigento_LoginAs_Model_Package */
                            $authPack = Mage::getSingleton('prxgt_lgas_model/package');
                            $authPack->setAdminName($operator);
                            $authPack->setCustomerId($customerId);
                            $authPack->setCustomerName($customerName);
                            $authPack->setRedirectUrl($url);
                            $validatorData = $session->getValidatorData();
                            $ip = $validatorData['remote_addr'];
                            $authPack->setIp($ip);
                            /** save login data to file */
                            $authPack->saveAsFile();
                            /** log event */
                            $log = Praxigento_LoginAs_Model_Logger::getLogger($this);
                            $log->trace("Operator '$operator' is redirected to front from ip '$ip' to login" .
                                " as customer '$customerName' ($customerId).");
                        }
                        $bu = var_export($this->getLayout()->getUpdate()->getHandles(), true);
                        /** load layout and render blocks */
                        $this->loadLayout()->renderLayout();
                    }
                }
            }
        }
    }
}