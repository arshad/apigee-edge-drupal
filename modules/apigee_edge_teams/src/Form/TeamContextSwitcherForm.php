<?php

/*
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\apigee_edge_teams\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for switching team.
 */
class TeamContextSwitcherForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * TeamContextSwitcherForm constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'team_context_switcher_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $teams = []) {
    /** @var \Drupal\apigee_edge_teams\Entity\TeamInterface $team */
    $options = ['' => $this->t('Select a team')];
    foreach ($teams as $team) {
      $value = $team->toUrl()->toString();
      $options[$value] = $team->label();
    }

    // Get the current team from the route to use as default value.
    /** @var \Drupal\apigee_edge_teams\Entity\TeamInterface $current_team */
    $current_team = $this->routeMatch->getParameter('team') ?? NULL;

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'container-inline',
        ],
      ],
    ];

    $form['wrapper']['context'] = [
      '#title' => $this->t('Select a team'),
      '#title_display' => 'invisible',
      '#type' => 'select',
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => $current_team ? $current_team->toUrl()
        ->toString() : NULL,
    ];

    $form['wrapper']['actions'] = [
      '#type' => 'actions',
    ];

    $form['wrapper']['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Go'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($context = $form_state->getValue('context')) {
      $form_state->setRedirectUrl(Url::fromUserInput($context));
    }
  }

}