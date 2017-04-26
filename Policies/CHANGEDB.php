<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = array();
$count = 0;

//v1.0.00
$sql[$count][0] = '1.0.00';
$sql[$count][1] = '-- First version, nothing to update';

//v1.0.01
++$count;
$sql[$count][0] = '1.0.01';
$sql[$count][1] = '';

//v1.0.02
++$count;
$sql[$count][0] = '1.0.02';
$sql[$count][1] = '';

//v1.0.03
++$count;
$sql[$count][0] = '1.0.03';
$sql[$count][1] = '';

//v1.0.04
++$count;
$sql[$count][0] = '1.0.04';
$sql[$count][1] = '';

//v1.0.05
++$count;
$sql[$count][0] = '1.0.05';
$sql[$count][1] = '';

//v1.0.06
++$count;
$sql[$count][0] = '1.0.06';
$sql[$count][1] = '';

//v1.0.07
++$count;
$sql[$count][0] = '1.0.07';
$sql[$count][1] = '';

//v1.0.08
++$count;
$sql[$count][0] = '1.0.08';
$sql[$count][1] = '';

//v1.0.09
++$count;
$sql[$count][0] = '1.0.09';
$sql[$count][1] = '';

//v1.0.10
++$count;
$sql[$count][0] = '1.0.10';
$sql[$count][1] = '';

//v1.0.11
++$count;
$sql[$count][0] = '1.0.11';
$sql[$count][1] = "
UPDATE gibbonAction SET category='Policies' WHERE gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Policies');end
";

//v1.0.12
++$count;
$sql[$count][0] = '1.0.12';
$sql[$count][1] = '';

//v1.0.13
++$count;
$sql[$count][0] = '1.0.13';
$sql[$count][1] = '';
