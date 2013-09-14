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

if (isActionAccessible($guid, $connection2, "/modules/Policies/policies_view.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	$highestAction=getHighestGroupedAction($guid, $_GET["q"], $connection2) ;
	if ($highestAction==FALSE) {
		print "<div class='error'>" ;
		print "The highest grouped action cannot be determined." ;
		print "</div>" ;
	}
	else {
		print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>View Policies</div>" ;
		print "</div>" ;
		
		$allPolicies=$_GET["allPolicies"] ;
	
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
	
		print "<p>" ;
			if ($highestAction=="View Policies_all") {
				print "On this page you can see all policies for which you are a member of the designated audience. To view a policy, click on its name in the left-hand column below. As a privileged user, you can also override audience restrictions, and view all policies." ;
			}
			else {
				print "On this page you can see all policies for which you are a member of the designated audience. To view a policy, click on its name in the left-hand column below." ;
			}
		print "</p>" ;
		
		if ($highestAction=="View Policies_all") {
			print "<h3 class='top'>" ;
			print "Filters" ;
			print "</h3>" ;
			?>
			<form method="get" action="<? print $_SESSION[$guid]["absoluteURL"]?>/index.php">
				<table style="width: 100%">	
					<tr>
						<td> 
							<b>All Policies</b><br/>
							<span style="font-size: 90%"><i>Override audience to reveal all policies.</i></span>
						</td>
						<td class="right">
							<?
							$checked="" ;
							if ($allPolicies=="on") {
								$checked="checked" ;
							}
							print "<input $checked name=\"allPolicies\" id=\"allPolicies\" type=\"checkbox\">" ;
							?>
						</td>
					</tr>
					<tr>
						<td colspan=2 class="right">
							<input type="hidden" name="q" value="/modules/<? print $_SESSION[$guid]["module"] ?>/policies_view.php">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?
		}
	
		try {
			if ($allPolicies=="on") {
				$data=array();  
				$sql="SELECT policiesPolicy.*, gibbonDepartment.name AS department, gibbonPerson.surname, gibbonPerson.preferredName, gibbonPerson.title FROM policiesPolicy JOIN gibbonPerson ON (policiesPolicy.gibbonPersonIDCreator=gibbonPerson.gibbonPersonID) LEFT JOIN gibbonDepartment ON (policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE active='Y' ORDER BY scope, gibbonDepartment.name, category, policiesPolicy.name" ; 
			}
			else {
				$data=array() ;
				$idCount=0 ;
				$idWhere="(" ;
				foreach ($_SESSION[$guid]["gibbonRoleIDAll"] AS $id) {
					$data["id$idCount"]="%" . trim($id[0]) . "%" ;
					$idWhere.="gibbonRoleIDList LIKE :id$idCount OR " ;
					$idCount++ ;
				}
				if ($idWhere=="(") {
					$idWhere=="" ;
				}
				else {
					$idWhere=substr($idWhere,0,-4) . ")" ;
				}
				
				$sql="SELECT policiesPolicy.*, gibbonDepartment.name AS department, gibbonPerson.surname, gibbonPerson.preferredName, gibbonPerson.title FROM policiesPolicy JOIN gibbonPerson ON (policiesPolicy.gibbonPersonIDCreator=gibbonPerson.gibbonPersonID) LEFT JOIN gibbonDepartment ON (policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE $idWhere ORDER BY scope, gibbonDepartment.name, category, policiesPolicy.name" ; 
			}
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
	
		if ($result->rowCount()<1) {
			print "<div class=\"error\">" ;
				print "There are no policies for you to view" ;
			print "</div>" ;
		}
		else {
			$lastHeader="" ;
			$headerCount=0 ;
			while ($row=$result->fetch()) {
				if ($count%2==0) {
					$rowNum="even" ;
				}
				else {
					$rowNum="odd" ;
				}
				$count++ ;
				
				if ($row["scope"]=="School") {
					$currentHeader="School" ;
				}
				else {
					$currentHeader=$row["department"] ;
				}
			
				if ($currentHeader!=$lastHeader) {
					if ($lastHeader!="") {
						print "</tr>" ;
						print "</table>" ;
					}
					
					print "<h2>$currentHeader</h2>" ;
				
					$count=0;
					$rowNum="odd" ;
					print "<table style='width: 100%'>" ;
					print "<tr class='head'>" ;
						print "<th style='width: 200px'>" ;
							print "Name<br/><span style='font-style: italic; font-size: 85%'>Short Name</span>" ;
						print "</th>" ;
						print "<th style='width: 200px'>" ;
							print "Category" ;
						print "</th>" ;
						print "<th style='width: 150px'>" ;
							print "Audience" ;
						print "</th>" ;
						print "<th style='width: 200px'>" ;
							print "Created By" ;
						print "</th>" ;
						print "<th style='width: 60px'>" ;
							print "Action" ;
						print "</th>" ;
					print "</tr>" ;
				
					$headerCount++ ;
				}
			
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
						print "<b>" . $row["category"] . "</b>" ;
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
						print formatName($row["title"], $row["preferredName"], $row["surname"], "Staff") ;
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
			
				$lastHeader=$currentHeader ;
			}
			print "</tr>" ;
			print "</table>" ;
		}
	}	
}	
?>