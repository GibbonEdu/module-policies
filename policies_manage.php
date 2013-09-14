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

session_start() ;

//Module includes
include "./modules/IB PYP/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Policies/policies_manage.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Manage Policies</div>" ;
	print "</div>" ;
	
	$deleteReturn = $_GET["deleteReturn"] ;
	$deleteReturnMessage ="" ;
	$class="error" ;
	if (!($deleteReturn=="")) {
		if ($deleteReturn=="success0") {
			$deleteReturnMessage ="Delete was successful." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $deleteReturnMessage;
		print "</div>" ;
	}
	
	//Set pagination variable
	$page=$_GET["page"] ;
	if ((!is_numeric($page)) OR $page<1) {
		$page=1 ;
	}
	
	//Build role lookup array
	$allRoles=array() ;
	try {
		$dataRoles=array();  
		$sqlRoles="SELECT * FROM gibbonRole" ; 
		$resultRoles=$connection2->prepare($sqlRoles);
		$resultRoles->execute($dataRoles);
	}
	catch(PDOException $e) { }
	while ($rowRoles=$resultRoles->fetch()) {
		$allRoles[$rowRoles["gibbonRoleID"]]=$rowRoles["name"] ;
	}
	
	print "<h2 class='top'>" ;
	print "Search" ;
	print "</h2>" ;
	print "<div class='linkTop'>" ;
	print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/policies_manage.php'>Clear Search</a>" ;
	print "</div>" ;
	?>
	<form method="get" action="<? print $_SESSION[$guid]["absoluteURL"]?>/index.php">
		<table style="width: 100%">	
			<tr><td style="width: 30%"></td><td></td></tr>
			<tr>
				<td> 
					<b>Search For</b><br/>
					<span style="font-size: 90%"><i>Name, Short Name, Category, Department.</i></span>
				</td>
				<td class="right">
					<input name="search" id="search" maxlength=20 value="<? print $_GET["search"] ?>" type="text" style="width: 300px">
				</td>
			</tr>
			<tr>
				<td colspan=2 class="right">
					<input type="hidden" name="q" value="/modules/<? print $_SESSION[$guid]["module"] ?>/policies_manage.php">
					<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
					<input type="submit" value="Submit">
				</td>
			</tr>
		</table>
	</form>
	
	<?
	print "<h2 class='top'>" ;
	print "View" ;
	print "</h2>" ;
	
	$search=$_GET["search"] ;
	
	try {
		$data=array();  
		$sql="SELECT policiesPolicy.*, gibbonDepartment.name AS department FROM policiesPolicy LEFT JOIN gibbonDepartment ON (policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) ORDER BY scope, gibbonDepartment.name, category, policiesPolicy.name" ; 
		if ($search!="") {
			$data=array("search1"=>"%$search%", "search2"=>"%$search%", "search3"=>"%$search%", "search4"=>"%$search%"); 
			$sql="SELECT policiesPolicy.*, gibbonDepartment.name AS department FROM policiesPolicy LEFT JOIN gibbonDepartment ON (policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE (policiesPolicy.name LIKE :search1 OR policiesPolicy.nameShort LIKE :search2 OR policiesPolicy.category LIKE :search3  OR gibbonDepartment.name LIKE :search4) ORDER BY scope, gibbonDepartment.name, category, policiesPolicy.name" ; 
		}
		$sqlPage= $sql . " LIMIT " . $_SESSION[$guid]["pagination"] . " OFFSET " . (($page-1)*$_SESSION[$guid]["pagination"]) ;
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { 
		print "<div class='error'>" . $e->getMessage() . "</div>" ; 
	}
	
	print "<div class='linkTop'>" ;
	print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Policies/policies_manage_add.php&search=" . $_GET["search"] . "'><img title='New' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.gif'/></a>" ;
	print "</div>" ;
		
	if ($result->rowCount()<1) {
		print "<div class='error'>" ;
		print "There are no policies to display." ;
		print "</div>" ;
	}
	else {
		if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
			printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "top", "search=$search") ;
		}
	
		print "<table style='width: 100%'>" ;
			print "<tr class='head'>" ;
				print "<th>" ;
					print "Name<br/><span style='font-style: italic; font-size: 85%'>Short Name</span>" ;
				print "</th>" ;
				print "<th>" ;
					print "Scope<br/><span style='font-style: italic; font-size: 85%'>Department</span>" ;
				print "</th>" ;
				print "<th>" ;
					print "Category" ;
				print "</th>" ;
				print "<th>" ;
					print "Audience" ;
				print "</th>" ;
				print "<th style='width: 100px'>" ;
					print "Actions" ;
				print "</th>" ;
			print "</tr>" ;
			
			$count=0;
			$rowNum="odd" ;
			try {
				$resultPage=$connection2->prepare($sqlPage);
				$resultPage->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
			while ($row=$resultPage->fetch()) {
				if ($count%2==0) {
					$rowNum="even" ;
				}
				else {
					$rowNum="odd" ;
				}
				$count++ ;
				
				if ($row["active"]=="N") {
					$rowNum="error" ;
				}

				//COLOR ROW BY STATUS!
				print "<tr class=$rowNum>" ;
					print "<td>" ;
						if ($row["type"]=="File") {
							print "<a style='font-weight: bold' href='" . $_SESSION[$guid]["absoluteURL"] . "/" . $row["location"] ."'>" . $row["name"] . "</a><br/>" ;
						}
						else if ($row["type"]=="Link") {
							print "<a style='font-weight: bold' target='_blank' href='" . $row["location"] ."'>" . $row["name"] . "</a><br/>" ;
						}
						print "<span style='font-style: italic; font-size: 85%'>" . $row["nameShort"]  . "</span>" ;
					print "</td>" ;
					print "<td>" ;
						print "<b>" . $row["scope"] . "</b><br/>" ;
						print "<span style='font-style: italic; font-size: 85%'>" . $row["department"]  . "</span>" ;
					print "</td>" ;
					print "<td>" ;
						print $row["category"] ;
					print "</td>" ;
					print "<td>" ;
						if ($row["gibbonRoleIDList"]=="") {
							print "<i>No audience set</i>" ;
						}
						else {
							$roles=explode(",", $row["gibbonRoleIDList"]) ;
							foreach ($roles AS $role) {
								print $allRoles[$role] . "<br/>" ;
							}
						}
					print "</td>" ;
					print "<td>" ;
						print "<script type='text/javascript'>" ;	
							print "$(document).ready(function(){" ;
								print "\$(\".comment-$count\").hide();" ;
								print "\$(\".show_hide-$count\").fadeIn(1000);" ;
								print "\$(\".show_hide-$count\").click(function(){" ;
								print "\$(\".comment-$count\").fadeToggle(1000);" ;
								print "});" ;
							print "});" ;
						print "</script>" ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Policies/policies_manage_edit.php&policiesPolicyID=" . $row["policiesPolicyID"] . "&search=" . $_GET["search"] . "'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Policies/policies_manage_delete.php&policiesPolicyID=" . $row["policiesPolicyID"] . "&search=" . $_GET["search"] . "'><img title='Delete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a> " ;
						if ($row["description"]!="") {
							print "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/page_down.png' title='Show Description' onclick='return false;' /></a>" ;
						}
					print "</td>" ;
				print "</tr>" ;
				if ($row["description"]!="") {
					print "<tr class='comment-$count' id='comment-$count'>" ;
						print "<td style='background-color: #fff; border-bottom: 1px solid #333' colspan=5>" ;
							print $row["description"] ;
						print "</td>" ;
					print "</tr>" ;
				}
			}
		print "</table>" ;
		
		if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
			printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "bottom", "search=$search") ;
		}
	}

}	
?>