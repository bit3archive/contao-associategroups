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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id: runonce.php 121 2010-07-08 06:24:16Z aschempp $
 */


class AssociateGroups extends Backend
{
	/**
	 * Run the controller
	 */
	public function hookSqlCompileCommands($return)
	{
		// fetch defined procedures
		$arrProcedures = $this->Database
			->prepare("SHOW PROCEDURE STATUS WHERE Db = ?")
			->execute($GLOBALS['TL_CONFIG']['dbDatabase'])
			->fetchEach('Name');

		// create procedure associategroups_member_to_group
		if (!in_array('associategroups_member_to_group', $arrProcedures)) {
			$return['CREATE'][] = 'CREATE PROCEDURE associategroups_member_to_group (IN P_TSTAMP INT, IN P_MEMBER_ID INT, IN P_GROUP_ARRAY BLOB)
  BEGIN
    -- clear the association table
	DELETE FROM tl_associategroups_member_to_group
	  WHERE member_id=P_MEMBER_ID
	    AND group_id NOT IN (SELECT id FROM tl_member_group
	                         WHERE P_GROUP_ARRAY REGEXP CONCAT(\'s:[0-9]*:"\', id, \'";\')
                                OR P_GROUP_ARRAY REGEXP CONCAT(\'i:\', id, \';\'));

    -- insert new association
    INSERT INTO tl_associategroups_member_to_group (tstamp, member_id, group_id)
      SELECT P_TSTAMP, P_MEMBER_ID, id FROM tl_member_group
        WHERE (  P_GROUP_ARRAY REGEXP CONCAT(\'s:[0-9]*:"\', id, \'";\')
              OR P_GROUP_ARRAY REGEXP CONCAT(\'i:\', id, \';\'))
           AND id NOT IN (SELECT group_id FROM tl_associategroups_member_to_group WHERE member_id=P_MEMBER_ID);
  END';
		}

		// create procedure associategroups_user_to_group
		if (!in_array('associategroups_user_to_group', $arrProcedures)) {
			$return['CREATE'][] = 'CREATE PROCEDURE associategroups_user_to_group (IN P_TSTAMP INT, IN P_USER_ID INT, IN P_GROUP_ARRAY BLOB)
  BEGIN
    -- clear the association table
	DELETE FROM tl_user_to_group
	  WHERE user_id=P_USER_ID
	    AND group_id NOT IN (SELECT id FROM tl_user_group
	                         WHERE P_GROUP_ARRAY REGEXP CONCAT(\'s:[0-9]*:"\', id, \'";\')
                                OR P_GROUP_ARRAY REGEXP CONCAT(\'i:\', id, \';\'));

    -- insert new association
    INSERT INTO tl_user_to_group (tstamp, user_id, group_id)
      SELECT P_TSTAMP, P_USER_ID, id FROM tl_user_group
        WHERE (  P_GROUP_ARRAY REGEXP CONCAT(\'s:[0-9]*:"\', id, \'";\')
              OR P_GROUP_ARRAY REGEXP CONCAT(\'i:\', id, \';\'))
           AND id NOT IN (SELECT group_id FROM tl_user_to_group WHERE user_id=P_USER_ID);
  END';
		}

		return $return;
	}


	public function hookMysqlMultiTriggerCreate($strTriggerName, $objTrigger, $return)
	{
		if ($objTrigger->table == 'tl_user') {
			$return['ALTER_CHANGE'][] = 'DELETE FROM tl_user_to_group';
			$return['ALTER_CHANGE'][] = "INSERT INTO `tl_user_to_group` (tstamp, user_id, group_id)
										 SELECT u.tstamp, u.id, g.id FROM tl_user u
										 INNER JOIN tl_user_group g
										 ON u.groups REGEXP CONCAT('s:[0-9]*:\"', g.id, '\";')
										 OR u.groups REGEXP CONCAT('i:', g.id, ';')";
		}

		if ($objTrigger->table == 'tl_member') {
			$return['ALTER_CHANGE'][] = 'DELETE FROM tl_member_to_group';
			$return['ALTER_CHANGE'][] = "INSERT INTO `tl_member_to_group` (tstamp, member_id, group_id)
										 SELECT m.tstamp, m.id, g.id FROM tl_member m
										 INNER JOIN tl_member_group g
										 ON m.groups REGEXP CONCAT('s:[0-9]*:\"', g.id, '\";')
										 OR m.groups REGEXP CONCAT('i:', g.id, ';')";
		}

		return $return;
	}
}
