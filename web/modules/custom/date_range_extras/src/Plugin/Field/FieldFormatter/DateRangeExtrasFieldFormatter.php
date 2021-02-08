<?php

namespace Drupal\date_range_extras\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime_range\Plugin\Field\FieldFormatter\DateRangeDefaultFormatter;

/**
 * Plugin implementation of the 'date_range_extras_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "date_range_extras_field_formatter",
 *   label = @Translation("Human date range"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeExtrasFieldFormatter extends DateRangeDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'separator' => '—',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $separator = $this->getSetting('separator');

    foreach ($items as $delta => $item) {
      if (!empty($item->start_date) && !empty($item->end_date)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
        $start_date = $item->start_date;
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
        $end_date = $item->end_date;

        if ($start_date->getTimestamp() !== $end_date->getTimestamp()) {
          $start_date_render = $this->buildDateWithIsoAttribute($start_date);

          // If start is less than now but end is greater, set start_date to 'Now'
          if ($start_date->getTimestamp() <= time()
            && $end_date->getTimestamp() > time()
          ) {
            // DateTimeFormatterBase::buildDateWithIsoAttribute wasn't flexible
            // enough, so this is a non-DRY solution for the time crunch.
            $iso_date = $start_date->format("Y-m-d\TH:i:s") . 'Z';
            $start_date_render = [
              '#theme' => 'time',
              '#text' => $this->t('Now'),
              '#attributes' => [
                'datetime' => $iso_date,
              ],
              '#cache' => [
                'contexts' => [
                  'timezone',
                ],
              ],
            ];
          }

          $elements[$delta] = [
            'start_date' => $start_date_render,
            'separator' => ['#plain_text' => $separator],
            'end_date' => $this->buildDateWithIsoAttribute($end_date),
          ];
        }
        else {
          $elements[$delta] = $this->buildDateWithIsoAttribute($start_date);

          if (!empty($item->_attributes)) {
            $elements[$delta]['#attributes'] += $item->_attributes;
            // Unset field item attributes since they have been included in the
            // formatter output and should not be rendered in the field template.
            unset($item->_attributes);
          }
        }
      }
    }

    return $elements;
  }
}
