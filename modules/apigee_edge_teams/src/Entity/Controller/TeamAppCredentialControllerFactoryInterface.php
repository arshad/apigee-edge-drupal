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

namespace Drupal\apigee_edge_teams\Entity\Controller;

/**
 * Base definition of the team app credential factory service.
 */
interface TeamAppCredentialControllerFactoryInterface {

  /**
   * Returns a preconfigured controller for the owner's app.
   *
   * @param string $owner
   *   The name of a team (company).
   * @param string $app_name
   *   Name of an app. (Not an app id, because app credentials endpoints does
   *   not allow to use them.)
   *
   * @return \Drupal\apigee_edge_teams\Entity\Controller\TeamAppCredentialControllerInterface
   *   The team app credentials controller.
   */
  public function teamAppCredentialController(string $owner, string $app_name): TeamAppCredentialControllerInterface;

}
