<?php

namespace Drupal\owlcarousel2\Entity;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\file\Entity\File;
use Drupal\owlcarousel2\OwlCarousel2Item;
use Drupal\user\UserInterface;

/**
 * Defines the OwlCarousel2 entity.
 *
 * @ingroup owlcarousel2
 *
 * @ContentEntityType(
 *   id = "owlcarousel2",
 *   label = @Translation("Carousel"),
 *   handlers = {
 *     "storage" = "Drupal\owlcarousel2\OwlCarousel2Storage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\owlcarousel2\OwlCarousel2ListBuilder",
 *     "views_data" = "Drupal\owlcarousel2\Entity\OwlCarousel2ViewsData",
 *     "translation" = "Drupal\owlcarousel2\OwlCarousel2TranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\owlcarousel2\Form\OwlCarousel2Form",
 *       "add" = "Drupal\owlcarousel2\Form\OwlCarousel2Form",
 *       "edit" = "Drupal\owlcarousel2\Form\OwlCarousel2Form",
 *       "delete" = "Drupal\owlcarousel2\Form\OwlCarousel2DeleteForm",
 *     },
 *     "access" = "Drupal\owlcarousel2\OwlCarousel2AccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\owlcarousel2\OwlCarousel2HtmlRouteProvider",
 *     },
 *   },
 *   base_table = "owlcarousel2",
 *   data_table = "owlcarousel2_field_data",
 *   revision_table = "owlcarousel2_revision",
 *   revision_data_table = "owlcarousel2_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer owlcarousel2 entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   },
 *   links = {
 *     "canonical" = "/admin/content/owlcarousel2/{owlcarousel2}",
 *     "add-form" = "/admin/content/owlcarousel2/add",
 *     "edit-form" = "/admin/content/owlcarousel2/{owlcarousel2}/edit",
 *     "delete-form" = "/admin/content/owlcarousel2/{owlcarousel2}/delete",
 *     "version-history" = "/admin/content/owlcarousel2/{owlcarousel2}/revisions",
 *     "revision" = "/admin/content/owlcarousel2/{owlcarousel2}/revisions/{owlcarousel2_revision}/view",
 *     "revision_revert" = "/admin/content/owlcarousel2/{owlcarousel2}/revisions/{owlcarousel2_revision}/revert",
 *     "revision_delete" = "/admin/content/owlcarousel2/{owlcarousel2}/revisions/{owlcarousel2_revision}/delete",
 *     "translation_revert" = "/admin/content/owlcarousel2/{owlcarousel2}/revisions/{owlcarousel2_revision}/revert/{langcode}",
 *     "collection" = "/admin/content/owlcarousel2",
 *   },
 *   field_ui_base_route = "owlcarousel2.settings"
 * )
 */
class OwlCarousel2 extends RevisionableContentEntityBase implements OwlCarousel2Interface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert') {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete') {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the owlcarousel2
    // owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', (bool) $published);
    return $this;
  }

  /**
   * Update an carousel item.
   *
   * @param \Drupal\owlcarousel2\OwlCarousel2Item $item
   *   The item to be updated.
   */
  public function updateItem(OwlCarousel2Item $item) {
    $item = $item->getArray();
    $this->get('items')->__set($item['id'], $item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $items = [];

    // Try to load the translated items, otherwise load the default one.
    if ($this->hasTranslation($langcode)) {
      $carousel = $this->getTranslation($langcode);
      if (!$carousel->get('items')->isEmpty()) {
        $items = $carousel->get('items')->first()->getValue();
      }
    }
    else {
      if (!$this->get('items')->isEmpty()) {
        $items = $this->get('items')->first()->getValue();
      }
    }

    return $items;
  }

  /**
   * Get an carousel item in array form.
   *
   * @param string $item_id
   *   The idem UUID.
   *
   * @return array|null
   *   The item.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getItem($item_id): ?array {
    $items = [];
    if (!$this->get('items')->isEmpty()) {
      $items = $this->get('items')->first()->getValue();
    }
    return $items[$item_id] ?? NULL;
  }

  /**
   * Add one item in the carousel.
   *
   * @param \Drupal\owlcarousel2\OwlCarousel2Item $item
   *   The item to be added.
   */
  public function addItem(OwlCarousel2Item $item) {
    $items = $this->getItems();

    $item_array = $item->getArray();
    $item_array['weight'] = count($items) + 1;

    $this->get('items')->__set($item_array['id'], $item_array);
    return $this;
  }

  /**
   * Get the carousel settings.
   *
   * @return array
   *   The settings array.
   */
  public function getSettings() {
    return $this->get('settings')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    $owlcarousel2 = array_values($entities)[0];
    $items = $owlcarousel2->getItems();
    foreach ($items as $item) {
      if ($item['type'] == 'image') {
        $file = File::load($item['file_id']);
        if ($file instanceof File) {
          $file->delete();
        }
      }
    }

    $blocks = Block::loadMultiple();
    /** @var \Drupal\block\Entity\Block $block */
    foreach ($blocks as $block) {
      if ($block->getPluginId() == 'owlcarousel2_block' && $block->get('settings')['carousel_id'] == $owlcarousel2->id()) {
        $block->delete();
      }
    }

    parent::preDelete($storage, $entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The author user id of the carousel.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the carousel.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the carousel is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['items'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Items'))
      ->setDescription(t('The carousel items.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['settings'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Settings'))
      ->setDescription(t('The carousel settings.'))
      ->setDefaultValue(NULL)
      ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDefaultValue([]);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
