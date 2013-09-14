<?
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


session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/policies_manage_add.php&search=" . $_GET["search"] ;

if (isActionAccessible($guid, $connection2, "/modules/Policies/policies_manage_add.php")==FALSE) {
	//Fail 0
	$URL = $URL . "&addReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	$scope=$_POST["scope"] ;
	$gibbonDepartmentID=$_POST["gibbonDepartmentID"] ;
	if ($gibbonDepartmentID=="") {
		$gibbonDepartmentID=NULL ;
	}
	$name=$_POST["name"] ;
	$nameShort=$_POST["nameShort"] ;
	$active=$_POST["active"] ;
	$category=$_POST["category"] ;
	$description=$_POST["description"] ;
	$type=$_POST["type"] ;
	$link=$_POST["link"] ;
	$gibbonRoleIDList="" ;
	for ($i=0; $i<$_POST["roleCount"]; $i++) {
		if ($_POST["gibbonRoleID" . $i]!="") {
			$gibbonRoleIDList.=$_POST["gibbonRoleID" . $i] . "," ;
		}
	}
	if (substr($gibbonRoleIDList, -1)==",") {
		$gibbonRoleIDList=substr($gibbonRoleIDList, 0, -1) ;
	}
	
	if ($scope=="" OR ($scope=="Department" AND is_null($gibbonDepartmentID)) OR $name=="" OR $nameShort=="" OR $active=="" OR $type=="" OR ($type=="Link" AND $link=="")) {
		//Fail 3
		$URL = $URL . "&addReturn=fail3" ;
		header("Location: {$URL}");
	}
	else {
		$partialFail=FALSE ;
		if ($type=="Link") {
			$location=$link ;
		}
		else {
			//Check extension to see if allowed
			try {
				$dataExt=array("username"=>$username); 
				$sqlExt="SELECT * FROM gibbonFileExtension WHERE extension='". end(explode(".", $_FILES['file']["name"])) ."'";
				$resultExt=$connection2->prepare($sqlExt);
				$resultExt->execute($dataExt);
			}
			catch(PDOException $e) { 
				$partialFail=TRUE ;
			}
		
			if ($resultExt->rowCount()!=1) {
				$partialFail=TRUE ;
			}
			else {
				//Attempt file upload
				$time=mktime() ;
				if ($_FILES['file']["tmp_name"]!="") {
					//Check for folder in uploads based on today's date
					$path=$_SESSION[$guid]["absolutePath"] ; ;
					if (is_dir($path ."/uploads/" . date("Y", $time) . "/" . date("m", $time))==FALSE) {
						mkdir($path ."/uploads/" . date("Y", $time) . "/" . date("m", $time), 0777, TRUE) ;
					}
					$unique=FALSE;
					while ($unique==FALSE) {
						$suffix=randomPassword(16) ;
						$location="uploads/" . date("Y", $time) . "/" . date("m", $time) . "/policy_" . str_replace(' ','_',trim($name)) . "_$suffix" . strrchr($_FILES["file"]["name"], ".") ;
						if (!(file_exists($path . "/" . $location))) {
							$unique=TRUE ;
						}
					}
				
					if (!(move_uploaded_file($_FILES["file"]["tmp_name"],$path . "/" . $location))) {
						//Fail 5
						$URL = $URL . "&addReturn=fail5" ;
						header("Location: {$URL}");
					}
				}
				else {
					$partialFail=TRUE ;
				}
			}
		}
		
		if ($partialFail==TRUE) {
			//Fail 5
			$URL = $URL . "&addReturn=fail5" ;
			header("Location: {$URL}");
			break ;
		}
		else {
			//Write to database
			try {
				$data=array("scope"=>$scope, "gibbonDepartmentID"=>$gibbonDepartmentID, "name"=>$name, "nameShort"=>$nameShort, "active"=>$active, "category"=>$category, "description"=>$description, "type"=>$type, "location"=>$location, "gibbonRoleIDList"=>$gibbonRoleIDList, "gibbonPersonIDCreator"=>$_SESSION[$guid]["gibbonPersonID"], "timestampCreated"=>date("Y-m-d H:i:s"));  
				$sql="INSERT INTO policiesPolicy SET scope=:scope, gibbonDepartmentID=:gibbonDepartmentID, name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, type=:type, location=:location, gibbonRoleIDList=:gibbonRoleIDList, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreated=:timestampCreated" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);  
			}
			catch(PDOException $e) {
				//Fail 2
				$URL = $URL . "&addReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}

			//Success 0
			$URL = $URL . "&addReturn=success0" ;
			header("Location: {$URL}");
		}
	}
}
?>