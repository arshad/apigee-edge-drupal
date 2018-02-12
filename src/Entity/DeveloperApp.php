<?php
/**
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

namespace Drupal\apigee_edge\Entity;

use Apigee\Edge\Api\Management\Entity\DeveloperApp as EdgeDeveloperApp;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Defines the Developer app entity class.
 *
 * @\Drupal\apigee_edge\Annotation\EdgeEntityType(
 *   id = "developer_app",
 *   label = @Translation("Developer App"),
 *   label_singular = @Translation("Developer App"),
 *   label_plural = @Translation("Developer Apps"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Developer App",
 *     plural = "@count Developer Apps",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\apigee_edge\Entity\Storage\DeveloperAppStorage",
 *     "access" = "Drupal\apigee_edge\Entity\EdgeEntityAccessControlHandler",
 *     "permission_provider" = "Drupal\apigee_edge\Entity\EdgeEntityPermissionProviderBase",
 *     "form" = {
 *       "default" = "Drupal\apigee_edge\Entity\Form\DeveloperAppCreateForm",
 *       "add" = "Drupal\apigee_edge\Entity\Form\DeveloperAppCreateForm",
 *       "add_for_developer" = "Drupal\apigee_edge\Entity\Form\DeveloperAppCreateFormForDeveloper",
 *       "edit" = "Drupal\apigee_edge\Entity\Form\DeveloperAppEditForm",
 *       "edit_for_developer" = "Drupal\apigee_edge\Entity\Form\DeveloperAppEditForm",
 *       "delete" = "Drupal\apigee_edge\Entity\Form\DeveloperAppDeleteForm",
 *       "delete_for_developer" = "Drupal\apigee_edge\Entity\Form\DeveloperAppDeleteFormForDeveloper",
 *     },
 *     "list_builder" = "Drupal\apigee_edge\Entity\ListBuilder\DeveloperAppListBuilder",
 *   },
 *   links = {
 *     "canonical" = "/developer-apps/{developer_app}",
 *     "collection" = "/developer-apps",
 *     "add-form" = "/developer-apps/add",
 *     "edit-form" = "/developer-apps/{developer_app}/edit",
 *     "delete-form" = "/developer-apps/{developer_app}/delete",
 *     "canonical-by-developer" = "/user/{user}/apps/{app}",
 *     "collection-by-developer" = "/user/{user}/apps",
 *     "add-form-for-developer" = "/user/{user}/apps/add",
 *     "edit-form-for-developer" = "/user/{user}/apps/{app}/edit",
 *     "delete-form-for-developer" = "/user/{user}/apps/{app}/delete",
 *   },
 *   entity_keys = {
 *     "id" = "appId",
 *   },
 *   permission_granularity = "entity_type",
 *   admin_permission = "administer developer_app",
 *   field_ui_base_route = "apigee_edge.settings.app",
 * )
 */
class DeveloperApp extends EdgeDeveloperApp implements DeveloperAppInterface {

  use FieldableEdgeEntityBaseTrait {
    id as private traitId;
    urlRouteParameters as private traitUrlRouteParameters;
    baseFieldDefinitions as private traitBaseFieldDefinitions;
  }

  /**
   * The Drupal user ID which belongs to the developer app.
   *
   * @var null|int
   */
  protected $drupalUserId;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values = []) {
    $values = array_filter($values);
    parent::__construct($values);
    $this->entityTypeId = 'developer_app';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $definitions */
    $definitions = self::traitBaseFieldDefinitions($entity_type);
    $developer_app_singular_label = \Drupal::entityTypeManager()->getDefinition('developer_app')->getSingularLabel();
    unset($definitions['credentials']);

    $definitions['name']->setRequired(TRUE);

    $definitions['displayName']
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 0,
      ])
      ->setLabel(t('@developer_app name', ['@developer_app' => $developer_app_singular_label]))
      ->setRequired(TRUE);

    $definitions['callbackUrl']
      ->setDisplayOptions('form', [
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 2,
      ])
      ->setLabel(t('Callback URL'));

    $definitions['description']
      ->setDisplayOptions('form', [
        'weight' => 1,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 4,
      ]);

    $definitions['status']
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 1,
      ])
      ->setLabel(t('@developer_app status', ['@developer_app' => $developer_app_singular_label]));

    $definitions['createdAt']
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 3,
      ])
      ->setLabel(t('Created'));

    $definitions['lastModifiedAt']
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 5,
      ])
      ->setLabel(t('Last updated'));

    // Hide readonly properties from Manage form display list.
    $read_only_fields = [
      'appId',
      'appFamily',
      'createdAt',
      'createdBy',
      'developerId',
      'displayName',
      'lastModifiedAt',
      'lastModifiedBy',
      'name',
      'scopes',
      'status',
    ];
    foreach ($read_only_fields as $field) {
      $definitions[$field]->setDisplayConfigurable('form', FALSE);
    }

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): ? string {
    return $this->getAppId();
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->drupalUserId === NULL ? NULL : User::load($this->drupalUserId);
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->drupalUserId = $account->id();
    // TODO What should we do if id is missing from the user?
    $this->developerId = $account->get('apigee_edge_developer_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->drupalUserId;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->drupalUserId = $uid;
    $user = User::load($uid);
    // TODO Should we throw an exception if the user can not be loaded?
    if ($user) {
      $this->developerId = $user->get('apigee_edge_developer_id')->target_id;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $params = $this->traitUrlRouteParameters($rel);
    if ($rel === 'add-form-for-developer') {
      $params['user'] = $this->drupalUserId;
      unset($params['developer_app']);
    }
    elseif ($rel === 'collection-by-developer') {
      $params['user'] = $this->drupalUserId;
      unset($params['developer_app']);
    }
    elseif (in_array($rel, [
      'canonical-by-developer',
      'edit-form-for-developer',
      'delete-form-for-developer',
    ])) {
      $params['user'] = $this->drupalUserId;
      $params['app'] = $this->getName();
      unset($params['developer_app']);
    }
    elseif ($rel === 'add-form') {
      unset($params['developerId']);
    }

    return $params;
  }

  public function isLatestRevision() {
    return FALSE;
  }

}
