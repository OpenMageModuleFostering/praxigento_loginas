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
 * Initial installation script. Contains all upgrades
 */
/** include function to replace old columns with new ones */
include_once('prxgt_install_func.php');


const SQL_TYPE_VARCHAR = 'VARCHAR(255) DEFAULT NULL';

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/** @var $coreSetup Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$coreSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
/** @var $conn Varien_Db_Adapter_Interface */
$conn = $installer->getConnection();

/** ****************************************************************************************************************
 * Create table columns for static attributes
 ***************************************************************************************************************** */

/** Sales Order */
prxgt_install_recreate_column($conn, $this->getTable('sales/order'),
    Praxigento_LoginAs_Config::ATTR_ORDER_CREATED_BY, SQL_TYPE_VARCHAR . ' ' .
        'COMMENT \'admin name/email if Praxigento Login As feature was used.\'');

/** Sales Order */
prxgt_install_recreate_column($conn, $this->getTable('sales/order_grid'),
    Praxigento_LoginAs_Config::ATTR_ORDER_CREATED_BY, SQL_TYPE_VARCHAR . ' ' .
        'COMMENT \'admin name/email if Praxigento Login As feature was used.\'');