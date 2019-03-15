<?php

/**
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\apigee_edge_teams\Plugin\Block;

use Drupal\apigee_edge_teams\Entity\TeamInterface;
use Drupal\apigee_edge_teams\TeamContextManagerInterface;
use Drupal\apigee_edge_teams\TeamMembershipManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block for switching team context.
 *
 * @Block(
 *   id = "apigee_edge_teams_team_switcher",
 *   admin_label = @Translation("Team switcher"),
 *   category = @Translation("Apigee Edge")
 * )
 */
class TeamContextSwitcherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $current_user;

  /**
   * The team context manager.
   *
   * @var \Drupal\apigee_edge_teams\TeamContextManagerInterface
   */
  protected $team_context_manager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $current_route_match;

  /**
   * The team membership manager.
   *
   * @var \Drupal\apigee_edge_teams\TeamMembershipManagerInterface
   */
  protected $team_membership_manager;

  /**
   * TeamContextSwitcher constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\apigee_edge_teams\TeamContextManagerInterface $team_context_manager
   *   The team context manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $current_user, TeamContextManagerInterface $team_context_manager, RouteMatchInterface $current_route_match, TeamMembershipManagerInterface $team_membership_manager ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->current_user = $current_user;
    $this->team_context_manager = $team_context_manager;
    $this->current_route_match = $current_route_match;
    $this->team_membership_manager = $team_membership_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('apigee_edge_teams.context_manager'),
      $container->get('current_route_match'),
      $container->get('apigee_edge_teams.team_membership_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($account->isAuthenticated());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($corresponding_route_name = $this->team_context_manager->correspondingRouteName()) {
      $current_route = $this->current_route_match->getRouteObject();
      $current_route_name = $this->current_route_match->getRouteName();
      $current_route_entity_type = $current_route->hasOption(TeamContextManagerInterface::DEVELOPER_ROUTE_OPTION_NAME) ? 'team' : 'user';
      $team_route = $current_route_entity_type === 'team' ? $current_route_name : $corresponding_route_name;
      $user_route = $current_route_entity_type === 'user' ? $current_route_name : $corresponding_route_name;
      $current_route_entity = $this->current_route_match->getParameter($current_route_entity_type);
      $current_route_parameters = $this->current_route_match->getRawParameters()->all();

      $build = [
        '#type' => 'dropbutton',
        '#attributes' => ['class' => ['apigee-team-context-selector']],
      ];

      $build['#links'] = [
        'current' => [
          'title' => $current_route_entity->label(),
          'attributes' => ['class' => ['apigee-team-context-current']],
        ],
      ];
      if ($current_route_entity_type === 'team') {
        $build['#links']['developers'] = [
          'title' => t('Developers:'),
          'attributes' => ['class' => ['apigee-team-context-group-label']],
        ];
        // Get all route parameters except the team.
        $parameters = ['user' => $this->current_user->id()] + array_diff_key($current_route_parameters, ['team' => NULL]);
        $build['#links']['current_developer'] = [
          'title' => $this->current_user->getDisplayName(),
          'url' => Url::fromRoute($user_route, $parameters),
        ];
      }
      $build['#links']['teams'] = [
        'title' => t('Teams:'),
        'attributes' => ['class' => ['apigee-team-context-group-label']],
      ];

      $teams = $this->team_membership_manager->getTeams($this->current_user->getEmail());

      foreach ($teams as $team) {
        if (!$current_route_entity instanceof TeamInterface || $team !== $current_route_entity->id()) {
          // Get all route parameters except the current user.
          $parameters = ['team' => $team] + array_diff_key($current_route_parameters, ['user' => NULL]);
          $build['#links'][$team] = [
            'title' => $team,
            'url' => Url::fromRoute($team_route, (array) $parameters),
          ];
        }
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user.permissions', 'url.path']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['team_list']);
  }

}
