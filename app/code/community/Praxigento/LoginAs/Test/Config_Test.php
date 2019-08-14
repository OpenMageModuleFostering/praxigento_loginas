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
 * Created by JetBrains PhpStorm.
 * User: Alex Gusev <flancer64@gmail.com>
 * Date: 2/18/13
 * Time: 6:05 PM
 */
class Praxigento_LoginAs_Test_ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testCfg()
    {
        // System / Configuration
        // general
        $this->assertTrue(is_bool(Praxigento_LoginAs_Config::cfgGeneralEnabled()));
        $this->assertTrue(is_bool(Praxigento_LoginAs_Config::cfgGeneralLogEvents()));
        // UI
        $this->assertTrue(is_bool(Praxigento_LoginAs_Config::cfgUiCustomersGridActionEnabled()));
        $this->assertTrue(is_bool(Praxigento_LoginAs_Config::cfgUiOrdersGridColumnEnabled()));
    }

    /**
     * Test default accessors for basic Magento components.
     */
    public function test_defaults()
    {
        $helper = Praxigento_LoginAs_Config::helper();
        $this->assertTrue($helper instanceof Mage_Core_Helper_Abstract);
    }

}
