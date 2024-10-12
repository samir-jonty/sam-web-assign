<?php

namespace Drupal\owlcarousel2\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a OwlCarousel2 revision.
 *
 * @ingroup owlcarousel2
 */
class OwlCarousel2RevisionDeleteForm extends ConfirmFormBase {

  use StringTranslationTrait;

  /**
   * The OwlCarousel2 revision.
   *
   * @var \Drupal\owlcarousel2\Entity\OwlCarousel2Interface
   */
  protected $revision;

  /**
   * The OwlCarousel2 storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $OwlCarousel2Storage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new OwlCarousel2RevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection, DateFormatterInterface $date_formatter) {
    $this->OwlCarousel2Storage = $entity_storage;
    $this->connection = $connection;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('owlcarousel2'),
      $container->get('database'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'owlcarousel2_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.owlcarousel2.version_history', ['owlcarousel2' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $owlcarousel2_revision = NULL) {
    $this->revision = $this->OwlCarousel2Storage->loadRevision($owlcarousel2_revision);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->OwlCarousel2Storage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('OwlCarousel2: deleted %title revision %revision.',
      [
        '%title' => $this->revision->label(),
        '%revision' => $this->revision->getRevisionId(),
      ]
    );

    $this->messenger()->addMessage(
      $this->t('Revision from %revision-date of OwlCarousel2 %title has been deleted.',
        [
          '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
          '%title' => $this->revision->label(),
        ]
      )
    );

    $form_state->setRedirect(
      'entity.owlcarousel2.canonical',
       ['owlcarousel2' => $this->revision->id()]
    );

    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {owlcarousel2_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.owlcarousel2.version_history',
         ['owlcarousel2' => $this->revision->id()]
      );
    }
  }

}
