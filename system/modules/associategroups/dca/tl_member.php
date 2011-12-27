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
 * @version    $Id: tl_member.php 120 2010-07-07 20:44:43Z aschempp $
 */


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['groups']['save_callback'][] = array('tl_member_associategroups', 'saveGroups');


class tl_member_associategroups extends Backend
{
	
	/**
	 * Save member groups to the association table
	 */
	public function saveGroups($varValue, $dc)
	{
		$arrGroups = deserialize($varValue);
		
		if (!is_array($arrGroups) || !count($arrGroups))
		{
			$this->Database->query("DELETE FROM tl_member_to_group WHERE member_id={$dc->id}");
		}
		else
		{
			$arrAssociations = $this->Database->execute("SELECT group_id FROM tl_member_to_group WHERE member_id={$dc->id}")->fetchEach('group_id');
			
			$arrDelete = array_diff($arrAssociations, $arrGroups);
			$arrInsert = array_diff($arrGroups, $arrAssociations);
			
			if (count($arrDelete) > 0)
			{
				$this->Database->query("DELETE FROM tl_member_to_group WHERE member_id={$dc->id} AND group_id IN (" . implode(',', $arrDelete) . ")");
			}
			
			if (count($arrInsert) > 0)
			{
				$time = time();
				$this->Database->query("INSERT INTO tl_member_to_group (tstamp,member_id,group_id) VALUES ($time,{$dc->id}," . implode("), ($time,{$dc->id},", $arrInsert) . ")");
			}
		}
		
		return $varValue;
	}
}

