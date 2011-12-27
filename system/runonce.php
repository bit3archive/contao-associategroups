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


class Runonce extends Frontend
{

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->import('Database');
	}


	/**
	 * Run the controller
	 */
	public function run()
	{
		$time = time();
		
		if (!$this->Database->tableExists('tl_member_to_group'))
		{
			$this->Database->query("CREATE TABLE `tl_member_to_group` (
									  `id` int(10) unsigned NOT NULL auto_increment,
									  `tstamp` int(10) unsigned NOT NULL default '0',
									  `member_id` int(10) unsigned NOT NULL default '0',
									  `group_id` int(10) unsigned NOT NULL default '0',
									  PRIMARY KEY  (`id`)
									) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
									
			$objMembers = $this->Database->execute("SELECT id,groups FROM tl_member WHERE groups!=''");
			
			while( $objMembers->next() )
			{
				$arrGroups = deserialize($objMembers->groups);
				
				if (is_array($arrGroups) && count($arrGroups))
				{
					$this->Database->query("INSERT INTO tl_member_to_group (tstamp,member_id,group_id) VALUES ($time,{$objMembers->id}," . implode("), ($time,{$objMembers->id},", $arrGroups) . ")");
				}
			}
		}
		
		
		if (!$this->Database->tableExists('tl_user_to_group'))
		{
			$this->Database->query("CREATE TABLE `tl_user_to_group` (
									  `id` int(10) unsigned NOT NULL auto_increment,
									  `tstamp` int(10) unsigned NOT NULL default '0',
									  `user_id` int(10) unsigned NOT NULL default '0',
									  `group_id` int(10) unsigned NOT NULL default '0',
									  PRIMARY KEY  (`id`)
									) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
									
			$objUsers = $this->Database->execute("SELECT id,groups FROM tl_user WHERE groups!=''");
			
			while( $objUsers->next() )
			{
				$arrGroups = deserialize($objUsers->groups);
				
				if (is_array($arrGroups) && count($arrGroups))
				{
					$this->Database->query("INSERT INTO tl_user_to_group (tstamp,user_id,group_id) VALUES ($time,{$objUsers->id}," . implode("), ($time,{$objUsers->id},", $arrGroups) . ")");
				}
			}
		}
	}
}


/**
 * Instantiate controller
 */
$objRunonce = new Runonce();
$objRunonce->run();

