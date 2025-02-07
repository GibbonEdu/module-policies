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

use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\User\RoleGateway;
use Gibbon\Module\Policies\PoliciesGateway;

//Module includes
include './modules/Policies/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    // Proceed!
    $page->breadcrumbs->add(__('Manage Policies'));

    $search = $_GET['search'] ?? '';

    $form = Form::create('search', $session->get('absoluteURL').'/index.php', 'get');
    $form->setTitle(__('Search'));
    $form->setClass('noIntBorder w-full');

    $form->addHiddenValue('q', '/modules/'.$session->get('module').'/policies_manage.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Name, Short Name, Category, Department.'));
        $row->addTextField('search')->setValue($search);

    $row = $form->addRow();
        $row->addSearchSubmit($session, __('Clear Search'));

    echo $form->getOutput();

    $roleGateway = $container->get(RoleGateway::class);

    $policiesGateway = $container->get(PoliciesGateway::class);
    $criteria = $policiesGateway
        ->newQueryCriteria()
        ->searchBy($policiesGateway->getSearchableColumns(), $search)
        ->sortBy(['scope', 'gibbonDepartment.name', 'category', 'policiesPolicy.name'])
        ->fromPOST();

    $policies = $policiesGateway->queryPolicies($criteria, $search);

    $table = DataTable::createPaginated('policies', $criteria);
    $table->setTitle(__('View'));

    $table->addMetaData('blankSlate', __('There are no policies to view.'));

    $table->addExpandableColumn('description')
        ->format(function ($policies) {
            $output = '';
            if (!empty($policies['description'])) {
                $output .= '<strong>'.__('Description').'</strong>:';
                $output .= '<br />'.$policies['description'];
            }
            return $output;
        })
        ->width('5%');

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Policies/policies_manage_add.php')
        ->addParam('search', $search)
        ->displayLabel();

    $table->modifyRows(function ($policies, $row) {
        if ($policies['active'] == 'N') {
            $row->addClass('error');
        }
        return $row;
    });

    $table->addColumn('scope', __('Scope'))
        ->format(function ($policies) {
            $output = '';
            $output .= '<strong>'.__($policies['scope']).'</strong>';
            $output .= '<br/>'.Format::small($policies['department']);
            return $output;
        })
        ->description(__('Department'))
        ->width('20%');

    $table->addColumn('name', __('Name'))
        ->format(function ($policies) {
            $output = '';
            $output .= '<strong>'.Format::link($policies['location'], $policies['name']).'</strong>';
            $output .= '<br/>'.Format::small($policies['nameShort']);
            return $output;
        })
        ->description(__('Name Short'))
        ->width('30%');

    $table->addColumn('category', __('Category'))->width('22%');

    $table->addColumn('gibbonRoleIDList', __('Audience'))
        ->format(function ($policies) use ($roleGateway) {
            $output = '';
            if ($policies['gibbonRoleIDList'] == '' && $policies['parent'] == 'N' && $policies['staff'] == 'N' && $policies['student'] == 'N') {
                $output .= '<i>'.__('No audience sets').'</i>';
            } else {
                if ($policies['gibbonRoleIDList'] != '') {
                    $roles = explode(',', $policies['gibbonRoleIDList']);
                    foreach ($roles as $role) {
                        $roleName = $roleGateway->getRoleByID($role);
                        if ($roleName) {
                            $output .= __($roleName['name'])."<br />";
                        }
                    }
                }
                if ($policies['parent'] == 'Y') {
                    $output .= __('Parents')."<br />";
                }
                if ($policies['staff'] == 'Y') {
                    $output .= __('Staff')."<br />";
                }
                if ($policies['student'] == 'Y') {
                    $output .= __('Students')."<br />";
                }
            }
            return $output;
        })
        ->width('20%');


    $table->addActionColumn()
        ->addParam('policiesPolicyID')
        ->addParam('search', $search)
        ->format(function ($policies, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
            ->setURL('/modules/Policies/policies_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
            ->setURL('/modules/Policies/policies_manage_delete.php');
        })
        ->width('20%');

    echo $table->render($policies);
}
