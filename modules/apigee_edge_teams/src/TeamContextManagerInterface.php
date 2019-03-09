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

/**
 * Describes the `apigee_edge_teams.context_manager` service.
 *
 * This service is responsible for understanding the context of the current
 * route. Context will either be developer context or team context.
 */
interface TeamContextManagerInterface {

  const DEVELOPER_ROUTE_OPTION_NAME = '_apigee_developer_route';
  const TEAM_ROUTE_OPTION_NAME = '_apigee_team_route';

  /**
   * Gets the corresponding route id for the current route.
   *
   * @return null|string
   *   The corresponding route ID if one is detected.
   */
  public function correspondingRouteId();

}
