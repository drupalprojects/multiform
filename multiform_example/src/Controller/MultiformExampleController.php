<?php
/**
 * @file
 * Contains \Drupal\multiform_example\Controller\MainController.
 */

namespace Drupal\multiform_example\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\multiform\MultiFormBuilder;

/**
 * Controller routines for contact routes.
 */
class MultiformExampleController implements ContainerInjectionInterface {

  protected $multiFormBuilder;

  public function __construct(MultiFormBuilder $formBuilder) {
    $this->multiFormBuilder = $formBuilder;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('multiform.form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content() {
    return $this->multiFormBuilder->getForm(array('Drupal\multiform_example\Form\Example1'), array('Drupal\multiform_example\Form\Example2'));
  }
}