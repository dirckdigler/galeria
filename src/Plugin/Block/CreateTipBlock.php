<?php
/**
 * @file
 * Contains \Drupal\mifamilia_forms\Plugin\Block\CreateTipBlock.
 */

namespace Drupal\mifamilia_forms\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;

/**
 * Provides a Create tip block.
 *
 * @Block(
 *   id = "mifamilia_forms_create_tip_block",
 *   admin_label = @Translation("Create tip block"),
 * )
 */
class CreateTipBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = \Drupal::config('mifamilia_forms.settings');
    $title  = $config->get('message_confirm');
    $body   = $config->get('message_confirm_body');
    $tocs   = $config->get('terms');
    $form['message_confirm'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Confirm message title'),
      '#description' => $this->t('This text will appear in the confirmation message title.'),
      '#default_value' => isset($title) ? Html::escape($title) : '',
    );
    $form['message_confirm_body'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Confirm message body'),
      '#description' => $this->t('This text will appear in the confirmation message body.'),
      '#default_value' => isset($body) ? Html::escape($body) : '',
    );
    $form['terms'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Terms and conditions'),
      '#description' => $this->t('This text will appear in the terms and conditions check.'),
      '#format' => isset($tocs['format']) ? $tocs['format'] : filter_default_format(),
      '#default_value' => isset($tocs['value']) ? $tocs['value'] : '',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $text = $form_state->getValue('terms');
    if($text['value'] == '') {
      drupal_set_message($this->t('Se necesitan los términos y condiciones'), 'error');
      $form_state->setErrorByName('terms', $this->t('Se necesitan los términos y condiciones'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('mifamilia_forms.settings');
    $to_save = array('message_confirm', 'message_confirm_body', 'terms');
    $values = $form_state->getValues();
    foreach ($values as $key => $value) {
      if (in_array($key, $to_save)) {
        $config->set($key, $value);
      }
    }
    $config->save();
    $session = \Drupal::request()->getSession();
    $session->getFlashBag()->add('swalMessage', array(
      'options'  => array(
        'title' => Html::escape($values['message_confirm']),
        'text'  => Html::escape($values['message_confirm_body']),
        'type'  => 'success',
        'confirmButtonText' => $this->t('Ok'),
      ),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\mifamilia_forms\Form\CreateTipForm', 0);
    return $form;
  }
}
