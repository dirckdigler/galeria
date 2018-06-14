<?php
/**
 * @file
 * Contains \Drupal\mifamilia_forms\Form\CreateTipForm.
 */

namespace Drupal\mifamilia_forms\Form;

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\contact\Entity\Message;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\sweetalert\Ajax\SweetAlertCommand;
use Drupal\Component\Utility\Html;

class CreateTipForm extends FormBase {

  const TAXONOMY = 'category';

  public function getFormId() {
    return 'mifamilia_forms_createtip_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $cat = 0) {
    $terms = $this->composeTerms(mifamilia_getTreeVocabulary(self::TAXONOMY));
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombres'),
      '#required' => TRUE,
    );
    $form['last_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Apellidos'),
      '#required' => TRUE,
    );
    $form['mail'] = array(
      '#type' => 'email',
      '#title' => $this->t('Correo electrónico'),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'number',
      '#title' => $this->t('Cédula'),
      '#required' => TRUE,
      '#max' => 9999999999,
      '#size' => 10,
    );
    $form['vehicle'] = array(
      '#type' => 'radios',
      '#title' => $this->t('¿Tienes Moto?'),
      '#options' => array(0 => $this->t('No'), 1 => $this->t('Yes')),
      '#required' => TRUE,
    );
    $form['vehicle_info'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Marca y Modelo de tu Moto'),
      '#required' => FALSE,
    );
    $form['post_data'] = array(
      '#type' => 'fieldset',
      '#title' => 'group',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['post_data']['category'] = array(
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#required' => TRUE,
      '#options' => $terms,
      '#empty_option' => '-- ' . $this->t('Select') . ' --',
      '#default_value' => $cat,
    );
    $form['post_data']['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
    );
    $form['post_data']['description'] = array(
      '#type' => 'textarea',
      '#attributes' => array(
        'placeholder' => t('Write a description...'),
      ),
      '#required' => TRUE,
    );
    $form['post_data']['image'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('Adjuntar imagen'),
      '#description' => $this->t('Allowed types: @extensions.', ['@extensions' => 'jpg jpeg png']),
      '#upload_validators' => array(
        'file_validate_extensions' => array('png jpg jpeg'),
      ),
      '#upload_location' => 'public://tips/images/',
    );
    $form['post_data']['youtube_video'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Adjuntar video de Youtube'),
      '#required' => FALSE,
      '#maxlength' => 255,
    );
    $config = \Drupal::config('mifamilia_forms.settings');
    $tocs   = $config->get('terms');
    $text   = isset($tocs['value'])  ? $tocs['value']  : 'Acepto términos y condiciones';
    $format = isset($tocs['format']) ? $tocs['format'] : filter_default_format();
    $form['tocs'] = array(
      '#type' => 'checkbox',
      '#title' => check_markup($text, $format),
      '#required' => TRUE,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send tip'),
      '#button_type' => 'primary',
    );
    $form['#cache']['max-age'] = 0;
    $form['#theme'] = 'mifamilia_forms_create_tip';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    //
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session = $this->getRequest()->getSession();
    $values = $form_state->getValues();
    $this->createTip($values);
    $config = \Drupal::config('mifamilia_forms.settings');
    $title = $config->get('message_confirm');
    $title = isset($title) ? $title : $this->t('Tip creado correctamente');
    $body  = $config->get('message_confirm_body');
    $body = isset($body) ? $body : $this->t('¡Pronto lo publicaremos!');
    $session->getFlashBag()->add('swalMessage', array(
      'options'  => array(
        'title' => Html::escape($title),
        'text'  => Html::escape($body),
        'type'  => 'success',
        'confirmButtonText' => $this->t('Ok'),
      ),
    ));
  }

  protected function createTip($values = array()) {
    $fields = array(
      'type' => 'tip',
      'created' => REQUEST_TIME,
      'changed' => REQUEST_TIME,
      'title'   => $values['title'],
      'field_terms' => TRUE,
      'field_last_name' => $values['last_name'],
      'field_category' => $values['category'],
      'field_mail' => $values['mail'],
      'field_nit' => $values['id'],
      'body' => [
        'summary' => '',
        'value' => $values['description'],
        'format' => 'plain_text',
      ],
      'field_make_model' => $values['vehicle_info'],
      'field_autor' => $values['name'],
      'field_have_motorcycle' => $values['vehicle'],
    );
    if ($values['image']) {
      $image = $values['image'];
      $file = File::load($image[0]);
      $file->status = FILE_STATUS_PERMANENT;
      $file->save();
      $fields['field_image'] = array(
        'target_id' => $file->id(),
        'alt' => $values['title'],
      );
    }
    if ($values['youtube_video']) {
      $video_id = youtube_get_video_id($values['youtube_video']);
      if ($video_id) {
        $fields['field_youtube_video'] = array(
          'input'    => $values['youtube_video'],
          'video_id' => $video_id,
        );
      }
    }
    $node = Node::create($fields);
    $node->save();
  }

  protected function composeTerms($terms) {
    $return = array();
    foreach ($terms as $key => $term) {
      $return[$term->tid] = $term->name;
    }
    return $return;
  }

}
