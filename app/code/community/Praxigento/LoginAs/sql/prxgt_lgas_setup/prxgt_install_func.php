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
 * Functions library to be used in install/upgrade scripts.
 *
 * User: Flancer
 * Date: 1/9/13
 * Time: 1:05 PM
 */
/** ===========================================================================================================================
 * Functions section below...
 * ========================================================================================================================== */
/**
 * Backup data for existing column, re-create column and move data back. Removes 'columnOld' in case of new name
 * for the column was applied.
 *
 * @param Varien_Db_Adapter_Interface $conn
 * @param                             $table
 * @param                             $column
 * @param                             $columnDef
 * @param null                        $columnOld old name for the column
 */
function prxgt_install_recreate_column(Varien_Db_Adapter_Pdo_Mysql $conn, $table, $column, $columnDef, $columnOld = null)
{
    $columnTmp = $column . '_tmp';
    $fetched   = $conn->fetchAll("SELECT * FROM $table LIMIT 1");

    // analyze old named column data
    $oldColumnExists = (!is_null($columnOld) && is_array($fetched) && isset($fetched[0]) && array_key_exists($columnOld, $fetched[0]));
    // analyze current column data
    $columnExists = (is_array($fetched) && isset($fetched[0]) && array_key_exists($column, $fetched[0]));
    // create backup column and backup data
    if ($columnExists || $oldColumnExists) {
        $conn->addColumn($table, $columnTmp, $columnDef);
        if ($oldColumnExists) {
            // backup old column data
            $conn->query("UPDATE  $table SET  $columnTmp = $columnOld");
        } else {
            // backup current column data
            $conn->query("UPDATE  $table SET  $columnTmp = $column");
        }
    }
    // re-create current column
    $conn->dropColumn($table, $column);
    $conn->addColumn($table, $column, $columnDef);
    // restore column data from backup
    if ($columnExists || $oldColumnExists) {
        // restore existed data
        $conn->query("UPDATE  $table SET $column = $columnTmp");
        $conn->dropColumn($table, $columnTmp);
    }
    // drop old column (for case of empty table)
    if (!is_null($columnOld) && ($oldColumnExists) && ($columnOld != $column)) {
        $conn->dropColumn($table, $columnOld);
    }
}