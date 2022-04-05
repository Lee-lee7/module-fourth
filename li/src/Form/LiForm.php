<?php

namespace Drupal\li\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the forms tables.
 */
class LiForm extends FormBase {

  /**
   * Variable to store the number of rows in tables.
   *
   * @var array
   */
  protected array $countR = [1];

  /**
   * Variable for storing the number of tables.
   *
   * @var int
   */
  protected int $countT = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'li_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add a wrapper to the form.
    $form['#prefix'] = '<div id="li-form">';
    $form['#suffix'] = '</div>';
    // Add style library.
    $form['#attached']['library'][] = 'li/style';
    // Call the table creation function.
    $this->createTable($form, $form_state);
    // Add a button to add a table.
    $form['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => ['::addTable'],
      '#name' => 'add_table',
      '#ajax' => [
        'event' => 'click',
        'progress' => 'none',
        'callback' => '::submitAjax',
        'wrapper' => 'li-form',
      ],
    ];
    // Add a submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'event' => 'click',
        'progress' => 'none',
        'callback' => '::submitAjax',
        'wrapper' => 'li-form',
      ],
    ];

    return $form;
  }

  /**
   * Build table.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structures.
   */
  public function createTable(array &$form, FormStateInterface $form_state) {
    // Array of form header fields.
    $header_title = [
      'year' => $this->t('Year'),
      'jan' => $this->t('Jan'),
      'feb' => $this->t('Feb'),
      'mar' => $this->t('Mar'),
      'q1' => $this->t('Q1'),
      'apr' => $this->t('Apr'),
      'may' => $this->t('May'),
      'jun' => $this->t('Jun'),
      'q2' => $this->t('Q2'),
      'jul' => $this->t('Jul'),
      'aug' => $this->t('Aug'),
      'sep' => $this->t('Sep'),
      'q3' => $this->t('Q3'),
      'oct' => $this->t('Oct'),
      'nov' => $this->t('Nov'),
      'dec' => $this->t('Dec'),
      'q4' => $this->t('Q4'),
      'ytd' => $this->t('YTD'),
    ];
    for ($t = 0; $t < $this->countT; $t++) {
      // Add a button to add year in table.
      $form["button_$t"] = [
        '#type' => 'submit',
        '#name' => $t,
        '#value' => $this->t('Add Year'),
        '#submit' => ['::addRow'],
        '#ajax' => [
          'event' => 'click',
          'progress' => 'none',
          'callback' => '::submitAjax',
          'wrapper' => 'li-form',
        ],
      ];
      // Create a table.
      $form["table_$t"] = [
        '#type' => 'table',
        '#header' => $header_title,
      ];
      for ($r = $this->countR[$t]; $r > 0; $r--) {
        // Create cells in each row.
        foreach ($header_title as $c) {
          // Assign number type cells.
          $form["table_$t"]["rows_$r"]["$c"] = [
            '#type' => 'number',
          ];
          // Inability to edit some cells.
          if (in_array("$c", ['Q1', 'Q2', 'Q3', 'Q4', 'YTD'])) {
            $form["table_$t"]["rows_$r"]["$c"] = [
              '#type' => 'number',
              '#disabled' => TRUE,
            ];
          }
        }
        // Assign a default value for the year column.
        $form["table_$t"]["rows_$r"]['Year'] = [
          '#type' => 'number',
          '#disabled' => TRUE,
          '#default_value' => date('Y') - $r + 1,
        ];
      }
    }
    return $form;
  }

  /**
   * Add a new row to the table.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structures.
   */
  public function addRow(array $form, FormStateInterface $form_state) {
    // Get the ID of the pressed button.
    $t = $form_state->getTriggeringElement()['#name'];
    // Increase the number of rows for the desired table.
    $this->countR[$t]++;
    // Calling the form construction function.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Add a new table.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structures.
   */
  public function addTable(array $form, FormStateInterface $form_state) {
    // Increase the number of tables.
    $this->countT++;
    // Set one row for the new table.
    $this->countR[] = 1;
    // Calling the form construction function.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Submit Ajax.
   */
  public function submitAjax(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate only when press the button submit.
    if ($form_state->getTriggeringElement()['#name'] !== 'submit') {
      return;
    }
    // Getting the values.
    $values = $form_state->getValues();
    // Find the table with the fewest rows.
    $min = array_search(min($this->countR), $this->countR);
    // Going through the whole table.
    for ($table = 0; $table < $this->countT; $table++) {
      // Additional variables.
      $value = 0;
      $empty = 0;
      // Cycle for tables.
      for ($row = 1; $row <= $this->countR[$table]; $row++) {
        // Going through rows.
        foreach (array_reverse($values["table_$table"]["rows_$row"]) as $key => $i) {
          // Disable validation for individual columns.
          if (in_array("$key", ['Year', 'Q1', 'Q2', 'Q3', 'Q4', 'YTD'])) {
            goto end;
          }
          // Check all other rows.
          if ($row <= $this->countR[$min]) {
            // Assigning a filled value.
            if (!$value && !$empty && $i !== "") {
              $value = 1;
            }
            // Assigning an empty value.
            if ($value && !$empty && $i == "") {
              $empty = 1;
            }
            // Error if tables do not matc.
            if ($values["table_$min"]["rows_$row"][$key] == "" && $i !== "" ||
              $values["table_$min"]["rows_$row"][$key] !== "" && $i == "") {
              $form_state->setErrorByName('', 'Invalid');
              break 3;
            }
            // Error finding a gap.
            if ($value && $empty && $i !== "") {
              $form_state->setErrorByName('', 'Invalid');
              break 3;
            }
          }
          end:
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Show error if available.
    if ($form_state->getErrors()) {
      $this->messenger()->addError('Invalid');
      $form_state->clearErrors();
    }
    else {
      // Going through the whole table.
      for ($t = 0; $t < $this->countT; $t++) {
        // Going through rows.
        for ($r = 1; $r <= $this->countR[$t]; $r++) {
          // Get data from a table row.
          $tr = $form_state->getValue(["table_$t", "rows_$r"]);
          // Variables for recording quarterly values.
          $q1 = $q2 = $q3 = $q4 = 0;
          // Calculate the value.
          if ($tr['Jan'] != "" || $tr['Feb'] != "" || $tr['Mar'] != "") {
            $q1 = round((($tr['Jan'] + $tr['Feb'] + $tr['Mar']) + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q1']['#value'] = $q1;
          }
          if ($tr['Apr'] != "" || $tr['May'] != "" || $tr['Jun'] != "") {
            $q2 = round((($tr['Apr'] + $tr['May'] + $tr['Jun']) + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q2']['#value'] = $q2;
          }
          if ($tr['Jul'] != "" || $tr['Aug'] != "" || $tr['Sep'] != "") {
            $q3 = round((($tr['Jul'] + $tr['Aug'] + $tr['Sep']) + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q3']['#value'] = $q3;
          }
          if ($tr['Oct'] != "" || $tr['Nov'] != "" || $tr['Dec'] != "") {
            $q4 = round((($tr['Oct'] + $tr['Nov'] + $tr['Dec']) + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q4']['#value'] = $q4;
          }
          if ($q1 || $q2 || $q3 || $q4) {
            $ytd = $q1 + $q2 + $q3 + $q4;
            $ytd = round(($ytd + 1) / 4, 2);
            $form["table_$t"]["rows_$r"]['YTD']['#value'] = $ytd;
          }
        }
      }
      // Successful validation message.
      $this->messenger()->addStatus('Valid');
    }
  }

}
