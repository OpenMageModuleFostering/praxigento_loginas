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
 * Time: 9:38 AM
 */
class Praxigento_LoginAs_Model_Observer extends Varien_Object
{
    /**
     * Extend UI blocks.
     * @param Varien_Event_Observer $observer
     */
    public function onAdminhtmlBlockHtmlBefore(Varien_Event_Observer $observer)
    {
        if (Praxigento_LoginAs_Config::cfgGeneralEnabled()) {
            $block = $observer->getData('block');
            if ($block instanceof Mage_Adminhtml_Block_Customer_Grid) {
                $this->doCustomerGridActionAdd($block);
            } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
                $this->doOrderGridColumnAdd($block);
            }
        }
    }

    /**
     * Adds 'Login As' button to the customer form.
     * @param Varien_Event_Observer $observer
     */
    public function onAdminhtmlWidgetContainerHtmlBefore(Varien_Event_Observer $observer)
    {
        /** validate availability & permissions */
        if (
            Praxigento_LoginAs_Config::cfgGeneralEnabled() &&
            Praxigento_LoginAs_Config::canAccessLoginAs()
        ) {
            /** add button to the form */
            /** @var $block Mage_Adminhtml_Block_Customer_Edit */
            $block = $observer->getEvent()->getBlock();
            if ($block instanceof Mage_Adminhtml_Block_Customer_Edit) {
                $customer = Mage::registry('current_customer');
                if ($customer && $customer->getId()) {
                    /** get data and setup URL to redirection controller */
                    $customerId = $customer->getId();
                    $url        = Mage::getModel('adminhtml/url')->getUrl(
                        Praxigento_LoginAs_Config::XMLCFG_ROUTER_ADMIN . Praxigento_LoginAs_Config::ROUTE_REDIRECT,
                        array(
                            Praxigento_LoginAs_Config::REQ_PARAM_LAS_ID => $customerId,
                            '_cache_secret_key', true /* NB-547 */
                        )
                    );
                    /** create UI button */
                    $level     = 0;
                    $sortOrder = -1;
                    $area      = 'header';
                    $block->addButton(Praxigento_LoginAs_Config::UI_BTN_LOGIN_AS,
                        array(
                            'label'   => Praxigento_LoginAs_Config::helper()->__('Login as Customer'),
                            /** open in new window */
                            'onclick' => "window.open('$url')",
                        ), $level, $sortOrder, $area);
                }
            }
        }
    }

    /**
     * Add 'created by' data to order.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onSalesConvertQuoteAddressToOrder(Varien_Event_Observer $observer)
    {
        if (Praxigento_LoginAs_Config::cfgGeneralEnabled()) {
            $order = $observer->getEvent()->getOrder();
            /** @var $session Mage_Customer_Model_Session */
            $session  = Mage::getSingleton('customer/session');
            $operator = $session->getData(Praxigento_LoginAs_Config::SESS_LOGGED_AS_OPERATOR);
            if (!is_null($operator)) {
                $order->setData(Praxigento_LoginAs_Config::ATTR_ORDER_CREATED_BY, $operator);
            } else {
                $order->setData(Praxigento_LoginAs_Config::ATTR_ORDER_CREATED_BY, '[Customer]');
            }
        }
    }

    /**
     * Add new action to customers grid.
     * @param Mage_Adminhtml_Block_Customer_Grid $block
     */
    private function doCustomerGridActionAdd(Mage_Adminhtml_Block_Customer_Grid $block)
    {
        /** validate availability & permissions */
        if (Praxigento_LoginAs_Config::cfgUiCustomersGridActionEnabled() &&
            Praxigento_LoginAs_Config::canAccessLoginAs()
        ) {
            /** add action link to grid */
            /** @var $cols  array */
            $cols = $block->getColumns();
            /** @var $colAction Mage_Adminhtml_Block_Widget_Grid_Column */
            $colAction = $cols['action'];
            $actions   = $colAction->getData('actions');
            if (is_array($actions)) {
                /** add new action */
                $actions[] = array(
                    'caption' => Praxigento_LoginAs_Config::helper()->__('Login as...'),
                    'url'     => array('base' => Praxigento_LoginAs_Config::XMLCFG_ROUTER_ADMIN . Praxigento_LoginAs_Config::ROUTE_REDIRECT),
                    'field'   => Praxigento_LoginAs_Config::REQ_PARAM_LAS_ID,
                    'target'  => '_blank',
                );
                $colAction->setData('actions', $actions);
                /** reset default renderer */
                $colAction->setData('renderer', 'adminhtml/customer_grid_renderer_multiaction');
            }
        }
    }

    /**
     * Add new column to orders grid.
     * @param Mage_Adminhtml_Block_Sales_Order_Grid $grid
     */
    private function doOrderGridColumnAdd(Mage_Adminhtml_Block_Sales_Order_Grid $grid)
    {
        /** validate availability & permissions */
        if (Praxigento_LoginAs_Config::cfgUiOrdersGridColumnEnabled() &&
            Praxigento_LoginAs_Config::canAccessCreatedBy()
        ) {
            /** define position for the column */
            $pos   = Praxigento_LoginAs_Config::cfgUiOrdersGridColumnPosition();
            $curr  = 0;
            $after = $grid->getLastColumnId();
            foreach ($grid->getColumns() as $key => $value) {
                $after = $key;
                if (++$curr >= $pos) {
                    break;
                }
            }
            /** add new column to grid */
            $grid->addColumnAfter(Praxigento_LoginAs_Config::ATTR_ORDER_CREATED_BY,
                array(
                    'header' => Praxigento_LoginAs_Config::helper()->__('Created by'),
                    'index'  => Praxigento_LoginAs_Config::ATTR_ORDER_CREATED_BY,
                    /** 'renderer' => 'nmmlm_core_block/adminhtml_widget_grid_column_renderer_pv', */
                    'type'   => 'text',
                ),
                $after
            );
            $grid->sortColumnsByOrder();
        }
    }
}