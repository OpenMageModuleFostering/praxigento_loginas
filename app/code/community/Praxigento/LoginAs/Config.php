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
 * Date: 13.10.2
 * Time: 09:07
 */
class Praxigento_LoginAs_Config
{
    /******************************************************************************************
     *                                   Attributes
     *****************************************************************************************/
    const ATTR_ORDER_CREATED_BY = 'prxgt_lgas_created_by';
    /******************************************************************************************
     *                           POST/GET request parameters
     *****************************************************************************************/
    /** don't change value: see Observer/doCustomerGridActionAdd() */
    const REQ_PARAM_LAS_ID       = 'id';
    const ROUTE_CUSTOMER_LOGINAS = '/login/as/';
    /******************************************************************************************
     *                            Routing (/[ctrl]/[action])
     *****************************************************************************************/
    const ROUTE_REDIRECT = '/redirect/';
    /** **************************************************************************************
     *                                Session parameters
     ************************************************************************************** */
    const SESS_LOGGED_AS_OPERATOR = 'prxgtLoggedInAsOperator';
    /******************************************************************************************
     *                                   UI components
     *****************************************************************************************/
    const UI_BTN_LOGIN_AS = 'prxgt_lgas_button';
    /******************************************************************************************
     *                            Module's config.xml parameters
     *****************************************************************************************/
    const XMLCFG_ROUTER_ADMIN = 'prxgt_lgas_admin';
    const XMLCFG_ROUTER_FRONT = 'prxgt_lgas_front';

    /**
     * 'true' - if admin user has right to view 'Created By' value.
     * @return bool
     */
    public static function canAccessCreatedBy()
    {
        return filter_var(Mage::getSingleton('admin/session')->isAllowed('sales/order/prxgt_lgas_created_by_access'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * 'true' - if admin user has right to use 'Login As' feature.
     * @return bool
     */
    public static function canAccessLoginAs()
    {
        return filter_var(Mage::getSingleton('admin/session')->isAllowed('customer/prxgt_lgas_access'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * 'true' - 'login as' feature is enabled, 'false' - otherwise.
     * @return bool
     */
    public static function cfgGeneralEnabled()
    {
        return filter_var(Mage::getStoreConfig('prxgt_lgas/general/enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * 'true' - log events using Magento logger or Log4php logger (in case of Nmmlm_Log module is installed).
     * @return bool
     */
    public static function cfgGeneralLogEvents()
    {
        return filter_var(Mage::getStoreConfig('prxgt_lgas/general/log_events'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Display or not 'Login as Customer' action on the customers grid.
     * @return bool
     */
    public static function cfgUiCustomersGridActionEnabled()
    {
        return filter_var(Mage::getStoreConfig('prxgt_lgas/ui/action_enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Display or not columns with 'Login As' data on the orders grid.
     * @return bool
     */
    public static function cfgUiOrdersGridColumnEnabled()
    {
        return filter_var(Mage::getStoreConfig('prxgt_lgas/ui/orders_grid_column_enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns position of the created by column (by default - 5).
     * @return int
     */
    public static function cfgUiOrdersGridColumnPosition()
    {
        $val = Mage::getStoreConfig('prxgt_lgas/ui/orders_grid_column_position');
        return (is_null($val)) ? 5 : (int)$val;
    }

    /**
     * Returns default helper for the module.
     * @return Praxigento_LoginAs_Helper_Data
     */
    public static function helper()
    {
        return Mage::helper('prxgt_lgas_helper');
    }
}
