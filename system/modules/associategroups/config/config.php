<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id: runonce.php 121 2010-07-08 06:24:16Z aschempp $
 */


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['sqlCompileCommands'][]      = array('AssociateGroups', 'hookSqlCompileCommands');
$GLOBALS['TL_HOOKS']['mysqlMultiTriggerCreate'][] = array('AssociateGroups', 'hookMysqlMultiTriggerCreate');


/**
 * Multi Triggers
 */
$GLOBALS['TL_TRIGGER']['tl_user']['after']['insert'][] = 'CALL associategroups_user_to_group(NEW.tstamp, NEW.id, NEW.groups);';
$GLOBALS['TL_TRIGGER']['tl_user']['after']['update'][] = 'CALL associategroups_user_to_group(NEW.tstamp, NEW.id, NEW.groups);';
$GLOBALS['TL_TRIGGER']['tl_user']['before']['delete'][] = 'DELETE FROM tl_user_to_group WHERE user_id=OLD.id;';
$GLOBALS['TL_TRIGGER']['tl_user_group']['before']['delete'][] = 'DELETE FROM tl_user_to_group WHERE group_id=OLD.id;';

$GLOBALS['TL_TRIGGER']['tl_member']['after']['insert'][] = 'CALL associategroups_member_to_group(NEW.tstamp, NEW.id, NEW.groups);';
$GLOBALS['TL_TRIGGER']['tl_member']['after']['update'][] = 'CALL associategroups_member_to_group(NEW.tstamp, NEW.id, NEW.groups);';
$GLOBALS['TL_TRIGGER']['tl_member']['before']['delete'][] = 'DELETE FROM tl_member_to_group WHERE member_id=OLD.id;';
$GLOBALS['TL_TRIGGER']['tl_member_group']['before']['delete'][] = 'DELETE FROM tl_member_to_group WHERE group_id=OLD.id;';
