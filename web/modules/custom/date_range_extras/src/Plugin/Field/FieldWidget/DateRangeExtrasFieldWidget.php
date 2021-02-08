<?php

namespace Drupal\date_range_extras\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDefaultWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'date_range_extras_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "date_range_extras_field_widget",
 *   module = "date_range_extras",
 *   label = @Translation("Weekday date selector"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeExtrasFieldWidget extends DateRangeDefaultWidget {

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $date_storage);

    $this->dateStorage = $date_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Add a validation constraint to show an error message when somebody picks
    // a weekend day for either field value.
    $element['#element_validate'][] = [$this, 'validateNotWeekend'];

    return $element;
  }

  /**
   * #element_validate callback to check neither the start nor end are weekend
   * days.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateNotWeekend(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $start_date = $element['value']['#value']['object'];
    $end_date = $element['end_value']['#value']['object'];

    if ($start_date instanceof DrupalDateTime && $end_date instanceof DrupalDateTime) {
      $start_day_of_week = $start_date->format('N');
      $end_day_of_week = $end_date->format('N');

      if ($start_day_of_week >= 6) {
        $form_state->setError($element, $this->t('The @title start date cannot be a weekend day', ['@title' => $element['#title']]));
      }

      if ($end_day_of_week >= 6) {
        $form_state->setError($element, $this->t('The @title end date cannot be a weekend day', ['@title' => $element['#title']]));
      }
    }
  }

}
