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

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $page->breadcrumbs->add(__('View Policies'));

        $allPolicies = '';

        if ($highestAction == 'View Policies_all') {
            echo '<p>'.__('On this page you can see all policies for which you are a member of the designated audience. To view a policy, click on its name in the left-hand column below. As a privileged user, you can also override audience restrictions, and view all policies.').'</p>';
        } else {
            echo '<p>'.__('On this page you can see all policies for which you are a member of the designated audience. To view a policy, click on its name in the left-hand column below.').'</p>';
        }

        if ($highestAction == 'View Policies_all') {
            $allPolicies = $_GET['allPolicies'] ?? '';

            $form = Form::create('search', $session->get('absoluteURL') . '/index.php', 'get');
            $form->setTitle(__('Filters'));
            $form->setClass('noIntBorder w-full');

            $form->addHiddenValue('q', '/modules/' . $session->get('module') . '/policies_view.php');

            $row = $form->addRow();
                $row->addLabel('allPolicies', __('All Policies'))->description(__('Override audience to reveal all policies.'));
                $row->addCheckbox('allPolicies')->checked($allPolicies);

            $row = $form->addRow();
                $row->addSearchSubmit($session, __('Clear Filters'));

            echo $form->getOutput();
        }

        $roleGateway = $container->get(RoleGateway::class);
        $policiesGateway = $container->get(PoliciesGateway::class);

        $criteria = $policiesGateway
            ->newQueryCriteria()
            ->sortBy(['scope', 'gibbonDepartment.name', 'category', 'policiesPolicy.name'])
            ->fromPOST();

        if ($allPolicies == 'on') {
            $policies = $policiesGateway->queryViewPoliciesByRole($criteria);
        } else {
            $policies = $policiesGateway->queryViewPoliciesByRole($criteria, $session->get('gibbonRoleIDCurrent'));
        }

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
            });

        $table->addColumn('name', __('Name'))
            ->format(function ($policies) {
                $output = '';
                $output .= '<strong>' . Format::link($policies['location'], $policies['name']) . '</strong>';
                $output .= '<br/>' . Format::small($policies['nameShort']);
                return $output;
            })
            ->description(__('Name Short'));

        $table->addColumn('scope', __('Scope'))
        ->format(function ($policies) {
            $output = '';
            $output .= '<strong>'.__($policies['scope']).'</strong>';
            $output .= '<br/>'.Format::small($policies['department']);
            return $output;
        })
        ->description(__('Department'));

        $table->addColumn('category', __('Category'))->width('18%');

        $table->addColumn('gibbonRoleIDList', __('Audience'))
            ->format(function ($policies) use ($roleGateway) {
                $output = '';
                if ($policies['gibbonRoleIDList'] == '' && $policies['parent'] == 'N' && $policies['staff'] == 'N' && $policies['student'] == 'N') {
                    $output .= '<i>' . __('No audience sets') . '</i>';
                } else {
                    if ($policies['gibbonRoleIDList'] != '') {
                        $roles = explode(',', $policies['gibbonRoleIDList']);
                        foreach ($roles as $role) {
                            $roleName = $roleGateway->getRoleByID($role);
                            if ($roleName) {
                                $output .= __($roleName['name']) . "<br />";
                            }
                        }
                    }
                    if ($policies['parent'] == 'Y') {
                        $output .= __('Parents') . "<br />";
                    }
                    if ($policies['staff'] == 'Y') {
                        $output .= __('Staff') . "<br />";
                    }
                    if ($policies['student'] == 'Y') {
                        $output .= __('Students') . "<br />";
                    }
                }
                return $output;
            });

        $table->addColumn('gibbonPersonIDCreator', __('Created By'))
            ->format(Format::using('name', ['title', 'preferredName', 'surname', 'Staff']));

        echo $table->render($policies);
    }
}
