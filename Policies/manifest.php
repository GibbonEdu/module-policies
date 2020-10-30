<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//This file describes the module, including database tables

//Basic variables
$name = 'Policies';
$description = 'A place to store, share and view school policies, either as files or links. Policies can be designated for access by particular audiences.';
$entryURL = 'policies_view.php';
$type = 'Additional';
$category = 'Other';
$version = '2.0.02';
$author = 'Ross Parker';
$url = 'http://rossparker.org';

//Module tables
$moduleTables[0] = "CREATE TABLE `policiesPolicy` (
  `policiesPolicyID` int(8) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `nameShort` varchar(14) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `active` enum('Y','N') NOT NULL,
  `scope` enum('School','Department') NOT NULL,
  `gibbonDepartmentID` int(4) unsigned zerofill DEFAULT NULL,
  `type` enum('File','Link') NOT NULL DEFAULT 'Link',
  `gibbonRoleIDList` varchar(255) NOT NULL,
  `staff` enum('N','Y') NOT NULL DEFAULT 'N',
  `student` enum('N','Y') NOT NULL DEFAULT 'N',
  `parent` enum('N','Y') NOT NULL DEFAULT 'N',
  `location` varchar(255) NOT NULL,
  `gibbonPersonIDCreator` int(8) unsigned zerofill NOT NULL,
  `timestampCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`policiesPolicyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";

//Action rows
$actionRows[0]['name'] = 'View Policies_byRole';
$actionRows[0]['precedence'] = '0';
$actionRows[0]['category'] = 'Policies';
$actionRows[0]['description'] = 'Allows user to view active policies according to their current role.';
$actionRows[0]['URLList'] = 'policies_view.php';
$actionRows[0]['entryURL'] = 'policies_view.php';
$actionRows[0]['defaultPermissionAdmin'] = 'N';
$actionRows[0]['defaultPermissionTeacher'] = 'Y';
$actionRows[0]['defaultPermissionStudent'] = 'Y';
$actionRows[0]['defaultPermissionParent'] = 'Y';
$actionRows[0]['defaultPermissionSupport'] = 'Y';
$actionRows[0]['categoryPermissionStaff'] = 'Y';
$actionRows[0]['categoryPermissionStudent'] = 'Y';
$actionRows[0]['categoryPermissionParent'] = 'Y';
$actionRows[0]['categoryPermissionOther'] = 'Y';

//Action rows
$actionRows[1]['name'] = 'View Policies_all';
$actionRows[1]['precedence'] = '1';
$actionRows[1]['category'] = 'Policies';
$actionRows[1]['description'] = 'Allows user to view all active policies.';
$actionRows[1]['URLList'] = 'policies_view.php';
$actionRows[1]['entryURL'] = 'policies_view.php';
$actionRows[1]['defaultPermissionAdmin'] = 'Y';
$actionRows[1]['defaultPermissionTeacher'] = 'N';
$actionRows[1]['defaultPermissionStudent'] = 'N';
$actionRows[1]['defaultPermissionParent'] = 'N';
$actionRows[1]['defaultPermissionSupport'] = 'N';
$actionRows[1]['categoryPermissionStaff'] = 'Y';
$actionRows[1]['categoryPermissionStudent'] = 'Y';
$actionRows[1]['categoryPermissionParent'] = 'Y';
$actionRows[1]['categoryPermissionOther'] = 'Y';

//Action rows
$actionRows[2]['name'] = 'Manage Policies';
$actionRows[2]['precedence'] = '0';
$actionRows[2]['category'] = 'Policies';
$actionRows[2]['description'] = 'Allows user to create and edit all policies.';
$actionRows[2]['URLList'] = 'policies_manage.php, policies_manage_add.php, policies_manage_edit.php, policies_manage_delete.php';
$actionRows[2]['entryURL'] = 'policies_manage.php';
$actionRows[2]['defaultPermissionAdmin'] = 'Y';
$actionRows[2]['defaultPermissionTeacher'] = 'N';
$actionRows[2]['defaultPermissionStudent'] = 'N';
$actionRows[2]['defaultPermissionParent'] = 'N';
$actionRows[2]['defaultPermissionSupport'] = 'N';
$actionRows[2]['categoryPermissionStaff'] = 'Y';
$actionRows[2]['categoryPermissionStudent'] = 'N';
$actionRows[2]['categoryPermissionParent'] = 'N';
$actionRows[2]['categoryPermissionOther'] = 'N';
