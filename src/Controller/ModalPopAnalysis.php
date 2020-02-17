<?php

namespace Drupal\modalpop\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

/**
 * Controller routines for modalpop analysis.
 *
 * @ingroup modalpop
 */
class ModalPopAnalysis extends ControllerBase {
  /**
   * Optional variable, the node id of a particular pop-up.
   *
   * @var int
   */
  private $nid;

  /**
   * Constructor.
   */
  public function __construct() {
    $nid = Drupal::request()->get("nid");
    $this->nid = $nid;
  }

  /**
   * A database query that returns a list of node titles and a count for each.
   *
   * @return array
   *   Associative array.
   */
  public function loadOverview() {
    $select = Database::getConnection()->select('modalpop_log', 'mpl');
    // Select these specific fields.
    $select->addField('mpl', 'nid');
    $select->addExpression('COUNT(*)', 'popcount');
    $select->groupBy('nid');
    // Now we retrieve all relevant data.
    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
    return $entries;
  }

  /**
   * A database query that returns all data for a single popup.
   *
   * @param int $nid
   *   $nid - Node id.
   *
   * @return mixed
   *   Associative array of all log entries for a specific node.
   */
  public function loadSingle(int $nid) {
    $select = Database::getConnection()->select('modalpop_log', 'mpl');
    // Select these specific fields.
    $select->fields('mpl');
    $select->condition('mpl.nid', $nid);
    $select->orderBy('mpl.whichdate');
    // Now we retrieve all relevant data.
    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
    return $entries;
  }

  /**
   * Returns some kind of description.
   */
  public function mpanalysis() {
    // Set the default date format for this site.
    // TODO: get default langcode of site and default date format.
    $date_format = 'd-m-Y';
    $renderer = Drupal::service('renderer');
    // If there is no nid in the URL.
    if (!$this->nid) {
      $data = $this->loadOverview();
      $headers = [
        $this->t('Popup title'),
        $this->t('Start'),
        $this->t('Stop'),
        $this->t('Views'),
      ];
      $rows = [];
      foreach ($data as $row) {
        // Load node object and get fields.
        $node = Node::load($row['nid']);
        $link = Link::createFromRoute(
          $node->get('title')->getString(),
          "modalpop.modal_pop_analysis",
          [
            "nid" => $node->id(),
          ],
          [
            "attributes" => [
              "class" => "modalpop-analysis-link",
              "id" => 'mpal-' . $node->id(),
            ],
          ]
        )->toRenderable();
        $rows[$row['nid']]['title'] = $renderer->renderRoot($link);
        $rows[$row['nid']]['popstart'] = date($date_format, strtotime($node->get('field_popstart')->getString()));
        $rows[$row['nid']]['popstop'] = date($date_format, strtotime($node->get('field_popstop')->getString()));
        $rows[$row['nid']]['popcount'] = $row['popcount'];
      }

      $build = [];
      $build['#type'] = 'table';
      $build['#attached']['library'][] = 'modalpop/modalpop-styles';
      $build['message'] = [
        '#markup' => $this->t('Below is a list of current and expired pop-ups.'),
      ];

      $build['#header'] = $headers;
      $build['#rows'] = $rows;
      $build['#empty'] = $this->t('No entries available');
      return $build;
    }
    // If there is a nid in the URL, show all data for that popup.
    else {
      $node = Node::load($this->nid);
      $type = is_object($node) ? $node->get('type')->getString() : 'not a node';
      if ($type != 'modalpop') {
        return [
          '#type' => 'markup',
          '#markup' => t('The node ID in the URL is not that of a modal pop-up.'),
        ];
      }
      $result = $this->loadSingle($this->nid);
      $data = [];

      foreach ($result as $row) {
        $row['month'] = date("m", $row['whichdate']);
        $row['day'] = date("z", $row['whichdate']);
        $data[] = $row;
      }

      $allbutts = [];
      $butt_labels = [
        1 => $node->get('field_pop_butt1_label')->getString(),
        2 => $node->get('field_pop_butt2_label')->getString(),
        3 => $node->get('field_pop_butt3_label')->getString(),
      ];
      $butt_labels[1] ? $allbutts['1: ' . $butt_labels[1]] = 0 : $allbutts;
      $butt_labels[2] ? $allbutts['2: ' . $butt_labels[2]] = 0 : $allbutts;
      $butt_labels[3] ? $allbutts['3: ' . $butt_labels[3]] = 0 : $allbutts;

      $total = $allbutts;
      $total_monthly = [];
      $total_daily = [];
      $prevmonth = 0;
      $prevday = 0;

      $mpbuild = [];
      $mpbuild['overall'] = [
        '#theme' => 'modalpop_monthly',
      ];

      // Work out overall total, monthly totals and daily totals.
      if (count($data) > 0) {
        foreach ($data as $key => $value) {
          // Specify the button.
          $buttID = "field_pop_butt" . $value['whichbutt'] . "_label";

          // Overall total so far.
          $total[$value['whichbutt'] . ": " . $node->get($buttID)->getString()]++;

          // Monthly total.
          // If it's a new month...
          if ($prevmonth != $value['month']) {
            // Populate this month with button placeholders.
            $total_monthly[$value['month']] = $allbutts;
          }
          $total_monthly[$value['month']][$value['whichbutt'] . ": " . $node->get($buttID)->getString()]++;
          $prevmonth = $value['month'];

          // Daily total.
          // If it's a new day...
          if ($prevday != $value['day']) {
            // Populate this month with button placeholders.
            $total_daily[$value['day']] = $allbutts;
          }
          $total_daily[$value['day']][$value['whichbutt'] . ": " . $node->get($buttID)->getString()]++;
          $prevday = $value['day'];

        }

        // OVERALL VIEW: a total with no days below it...
        $mpbuild['overall']['linktitle'] = [
          '#type' => 'link',
          '#title' => $node->get('title')->getString(),
          '#url' => $node->toUrl(),
        ];
        $mpbuild['overall']['header'] = [
          '#markup' => t('Overall totals'),
        ];
        $m = 1;
        $total_votes = array_sum($total);
        foreach ($total as $key => $value) {
          $percent = round(($value / $total_votes) * 100, 2);
          $mpbuild['overall']['m_totals'][$m]['percent'] = ['#markup' => $percent];
          $mpbuild['overall']['m_totals'][$m]['label'] = ['#markup' => $key];
          $mpbuild['overall']['m_totals'][$m]['value'] = ['#markup' => $value];
          $mpbuild['overall']['m_totals'][$m]['which'] = ['#markup' => $m];
          $m++;
        }

        // MONTHLY VIEW: month numbers with indented days below it...
        foreach ($data as $key => $value) {
          // Month header.
          $mpmonth = 'month_' . $value['month'];
          $mpbuild[$mpmonth] = [
            '#theme' => 'modalpop_monthly',
          ];
          $mpbuild[$mpmonth]['header'] = [
            '#markup' => date("F Y", $value['whichdate']),
          ];

          $mtotal = 0;
          foreach ($total_monthly[$value['month']] as $mkey => $mvalue) {
            $mtotal += $mvalue;
          }

          $m = 1;
          foreach ($total_monthly[$value['month']] as $mkey => $mvalue) {
            $percent = round(($mvalue / $mtotal) * 100, 2);
            $mpbuild[$mpmonth]['m_totals'][$m]['percent'] = ['#markup' => $percent];
            $mpbuild[$mpmonth]['m_totals'][$m]['label'] = ['#markup' => $mkey];
            $mpbuild[$mpmonth]['m_totals'][$m]['value'] = ['#markup' => $mvalue];
            $mpbuild[$mpmonth]['m_totals'][$m]['which'] = ['#markup' => $m];
            $m++;
          }

          // The daily data.
          $mpday = 'd_' . $value['day'];
          $days[$mpmonth][$mpday]['date'] = ['#markup' => date("l jS", $value['whichdate'])];
          $days[$mpmonth][$mpday]['whichday'] = ['#markup' => $value['day']];
          $dtotal = 0;
          foreach ($total_daily[$value['day']] as $dkey => $dvalue) {
            $dtotal += $dvalue;
          }
          $mpbuild[$mpmonth]['days_data']['#theme'] = 'modalpop_daily';
          $mpbuild[$mpmonth]['days_data']['whichmonth'] = ['#markup' => $value['month']];

          $d = 1;
          foreach ($total_daily[$value['day']] as $dkey => $dvalue) {
            $percent = round(($dvalue / $dtotal) * 100, 2);
            $days[$mpmonth][$mpday][$d] = [
              'label' => [
                '#markup' => $dkey,
              ],
              'clicks' => [
                '#markup' => $dvalue,
              ],
              'percent' => [
                '#markup' => $percent,
              ],
              'demipercent' => [
                '#markup' => $percent / 2,
              ],
            ];
            $d++;
          }
          $mpbuild[$mpmonth]['days_data']['days'] = $days[$mpmonth];
        }

      }
      else {
        $mpbuild['message'] .= [
          '#markup' => $this->t('no data to crunch - come back later'),
        ];
      }

      $mpbuild['#attached']['library'][] = 'modalpop/modalpop-styles';
      $mpbuild['#attached']['library'][] = 'modalpop/modalpop-scripts';
      return $mpbuild;
    }
  }

}
