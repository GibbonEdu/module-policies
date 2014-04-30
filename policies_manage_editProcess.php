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

include "../../functions.php" ;
include "../../config.php" ;

include "./moduleFunctions.php" ;

//New PDO DB connection
try {
    $connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
    echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$policiesPolicyID=$_GET["policiesPolicyID"] ;
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/policies_manage_edit.php&policiesPolicyID=$policiesPolicyID&search=" . $_GET["search"] ;

if (isActionAccessible($guid, $connection2, "/modules/Policies/policies_manage_edit.php")==FALSE) {
	//Fail 0
	$URL=$URL . "&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	//Check if school year specified
	if ($policiesPolicyID=="") {
		//Fail1
		$URL=$URL . "&updateReturn=fail1" ;
		header("Location: {$URL}");
	}
	else {
		try {
			$data=array("policiesPolicyID"=>$policiesPolicyID);  
			$sql="SELECT * FROM policiesPolicy WHERE policiesPolicyID=:policiesPolicyID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			//Fail2
			$URL=$URL . "&deleteReturn=fail2" ;
			header("Location: {$URL}");
			break ;
		}
		
		if ($result->rowCount()!=1) {
			//Fail 2
			$URL=$URL . "&updateReturn=fail2" ;
			header("Location: {$URL}");
		}
		else {
			//Validate Inputs
			$name=$_POST["name"] ;
			$nameShort=$_POST["nameShort"] ;
			$active=$_POST["active"] ;
			$category=$_POST["category"] ;
			$description=$_POST["description"] ;
			$gibbonRoleIDList="" ;
			for ($i=0; $i<$_POST["roleCount"]; $i++) {
				if (isset($_POST["gibbonRoleID" . $i])) {
					if ($_POST["gibbonRoleID" . $i]!="") {
						$gibbonRoleIDList.=$_POST["gibbonRoleID" . $i] . "," ;
					}
				}
			}
			if (substr($gibbonRoleIDList, -1)==",") {
				$gibbonRoleIDList=substr($gibbonRoleIDList, 0, -1) ;
			}
			
			if ($name=="" OR $nameShort=="" OR $active=="") {
				//Fail 3
				$URL=$URL . "&updateReturn=fail3" ;
				header("Location: {$URL}");
			}
			else {
				//Write to database
				try {
					$data=array("name"=>$name, "nameShort"=>$nameShort, "active"=>$active, "category"=>$category, "description"=>$description, "gibbonRoleIDList"=>$gibbonRoleIDList, "policiesPolicyID"=>$policiesPolicyID);  
					$sql="UPDATE policiesPolicy SET name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, gibbonRoleIDList=:gibbonRoleIDList WHERE policiesPolicyID=:policiesPolicyID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);  
				}
				catch(PDOException $e) { 
					//Fail 2
					$URL=$URL . "&updateReturn=fail5" ;
					header("Location: {$URL}");
					break ;
				}
				
				//Success 0
				$URL=$URL . "&updateReturn=success0" ;
				header("Location: {$URL}");
			}
		}
	}
}
?>