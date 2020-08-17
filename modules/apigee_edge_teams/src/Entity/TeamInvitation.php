<?php

/**
 * Copyright 2020 Google Inc.
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

namespace Drupal\apigee_edge_teams\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the team invitation entity.
 *
 * @ContentEntityType(
 *   id = "team_invitation",
 *   label = @Translation("Team Invitation"),
 *   label_collection = @Translation("Team invitations"),
 *   label_singular = @Translation("team invitation"),
 *   label_plural = @Translation("team invitations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count team invitation",
 *     plural = "@count team invitations",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\apigee_edge_teams\Entity\ListBuilder\TeamInvitationListBuilder",
 *     "storage" = "Drupal\apigee_edge_teams\Entity\Storage\TeamInvitationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\apigee_edge_teams\TeamInvitationViewsData",
 *     "access" = "Drupal\apigee_edge_teams\Entity\TeamInvitationAccessControlHandler",
 *     "permission_provider" = "Drupal\apigee_edge_teams\Entity\TeamInvitationPermissionProvider",
 *     "form" = {
 *       "delete" = "Drupal\apigee_edge_teams\Entity\Form\TeamInvitationDeleteForm",
 *       "accept" = "Drupal\apigee_edge_teams\Entity\Form\TeamInvitationAcceptForm",
 *       "decline" = "Drupal\apigee_edge_teams\Entity\Form\TeamInvitationDeclineForm",
 *     },
 *     "route_provider" = {
 *        "html" = "Drupal\apigee_edge_teams\Entity\TeamInvitationRouteProvider",
 *     },
 *   },
 *   base_table = "team_invitation",
 *   data_table = "team_invitation_field_data",
 *   admin_permission = "administer team invitations",
 *   entity_keys = {
 *     "id" = "uuid",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "/teams/{team}/invitations/{team_invitation}/delete",
 *     "accept-form" = "/teams/{team}/invitations/{team_invitation}/accept",
 *     "decline-form" = "/teams/{team}/invitations/{team_invitation}/decline",
 *   },
 * )
 */
class TeamInvitation extends ContentEntityBase implements TeamInvitationInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields[$entity_type->getKey('id')] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setReadOnly(TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The label of the invitation.'))
      ->setDefaultValue('')
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of the invitation.'))
      ->setDefaultValue(TeamInvitationInterface::STATUS_PENDING)
      ->setSetting('allowed_values', [
        TeamInvitationInterface::STATUS_PENDING => t('Pending'),
        TeamInvitationInterface::STATUS_ACCEPTED => t('Accepted'),
        TeamInvitationInterface::STATUS_DECLINED => t('Declined'),
        TeamInvitationInterface::STATUS_CANCELLED => t('Cancelled'),
      ])
      ->setRequired(TRUE);

    $fields['team'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Team'))
      ->setDescription(t('The team for this invitation.'))
      ->setSetting('target_type', 'team')
      ->setRequired(TRUE);

    $fields['team_roles'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Roles'))
      ->setDescription(t('The team roles for this invitation.'))
      ->setSetting('target_type', 'team_role')
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED);

    $fields['recipient'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Recipient'))
      ->setDescription(t('The email address of the invitee.'))
      ->setDefaultValue('')
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(): string {
    return $this->get('label')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel(string $label): TeamInvitationInterface {
    $this->set('label', $label);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTeam(): ?TeamInterface {
    return $this->get('team')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setTeam(TeamInterface $team): TeamInvitationInterface {
    $this->set('team', ['target_id' => $team->id()]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus(): int {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus(int $status): TeamInvitationInterface {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipient(): ?string {
    return $this->get('recipient')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRecipient(string $email): TeamInvitationInterface {
    $this->set('recipient', $email);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTeamRoles(): ?array {
    return $this->get('team_roles')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setTeamRoles(array $team_roles): TeamInvitationInterface {
    $this->set('team_roles', $team_roles);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPending(): bool {
    return $this->getStatus() === TeamInvitationInterface::STATUS_PENDING;
  }

  /**
   * {@inheritdoc}
   */
  public function isAccepted(): bool {
    return $this->getStatus() === TeamInvitationInterface::STATUS_ACCEPTED;
  }

  /**
   * {@inheritdoc}
   */
  public function isDeclined(): bool {
    return $this->getStatus() === TeamInvitationInterface::STATUS_DECLINED;
  }

  /**
   * {@inheritdoc}
   */
  public function isCancelled(): bool {
    return $this->getStatus() === TeamInvitationInterface::STATUS_CANCELLED;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Set a default label.
    if ($this->get('label')->isEmpty()) {
      $this->setLabel($this->t('Invitation to join @team as @roles.', [
        '@team' => $this->getTeam()->label(),
        '@roles' => implode(', ', array_map(function (TeamRoleInterface $team_role) {
          return $team_role->label();
        }, $this->getTeamRoles()))
      ]));
    }
  }

}