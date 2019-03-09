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

namespace Drupal\apigee_edge_teams;

use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Service that makes easier to work with company (team) memberships.
 *
 * It also handles cache invalidation.
 */
class TeamContextManager implements TeamContextManagerInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $current_route_match;

  /**
   * TeamContextManager constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $current_route_match) {
    $this->current_route_match = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function correspondingRouteName() {
    // Get the current route object.
    if ($current_route_object = $this->current_route_match->getRouteObject()) {
      // The corresponding team, developer route or null.
      return ($corresponding_developer_id = $current_route_object->getOption(static::DEVELOPER_ROUTE_OPTION_NAME))
        ? $corresponding_developer_id
        : $current_route_object->getOption(static::TEAM_ROUTE_OPTION_NAME);
    }
  }

}
