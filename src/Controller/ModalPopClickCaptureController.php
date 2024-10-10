<?php

namespace Drupal\modalpop\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller routines for modalpop.
 *
 * @ingroup modalpop
 */
class ModalPopClickCaptureController extends ControllerBase {

  /**
   * Request stack made by client.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack made by client.
   */
  public function __construct(
    RequestStack $request_stack
  ) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get("request_stack")
    );
  }

  /**
   * Get the POST data and prep it before passing it to the save function.
   *
   * @return array
   *   A render array containing the id of the log entry just created.
   */
  public function getClickCapture() {
    $data_payload = Drupal::request()->getContent();
    $data_decoded = [];

    foreach (explode('&', $data_payload) as $chunk) {
      $param = explode("=", $chunk);
      if ($param) {
        $data_decoded[$param[0]] = $param[1];
      }
    }

    $return = $this->setClickCapture($data_decoded);

    return [
      '#markup' => $return,
    ];
  }

  /**
   * Take the prepped data and insert into the modalpop_log table.
   *
   * @param array $data
   *   $data - prepped POST data.
   */
  public function setClickCapture(array $data) {
    $insert = Database::getConnection()->insert('modalpop_log');
    $insert->fields(['nid', 'uid', 'whichbutt', 'whichdate'], $data);
    $return = $insert->execute();
    return $return;
  }

}
