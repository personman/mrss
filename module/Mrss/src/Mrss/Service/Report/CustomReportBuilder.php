<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use Mrss\Entity\Report as ReportEntity;

/**
 * Class CustomReportBuilder
 *
 * Accept a CustomReport Entity and return everything needed to display the report
 *
 * @package Mrss\Service\Report
 */
class CustomReportBuilder extends Report
{
    public function build(ReportEntity $report)
    {
        $changed = false;
        $items = $report->getItems();

        foreach ($items as $item) {
            if (null == $item->getCache()) {

                $builder = $this
                    ->getChartBuilder($item->getConfig());

                $chart = $builder->getChart();
                $footnotes = $builder->getFootnotes();
                $footnotes = $this->footnoteSubstitutions($footnotes, $item->getYear());

                $cache = array(
                    'chart' => $chart,
                    'footnotes' => $footnotes
                );

                $item->setCache($cache);
                $this->getReportItemModel()->save($item);
                $changed = true;
            }
        }

        if ($changed) {
            $this->getReportItemModel()->getEntityManager()->flush();
        }
    }

    public function getChartBuilder($config)
    {
        $year = $config['year'];
        switch ($config['presentation']) {
            case 'scatter':
                /** @var \Mrss\Service\Report\ChartBuilder\BubbleBuilder $builder */
                $builder = $this->getServiceManager()->get('builder.bubble');
                break;

            case 'bubble':
                /** @var \Mrss\Service\Report\ChartBuilder\BubbleBuilder $builder */
                $builder = $this->getServiceManager()->get('builder.bubble');
                break;
            case 'line':
                /** @var \Mrss\Service\Report\ChartBuilder\LineBuilder $builder */
                $builder = $this->getServiceManager()->get('builder.line');
                break;
            default:
                throw new \Exception('Unknown chart type.');
                break;
        }

        $builder->setYear($year);
        $builder->setConfig($config);

        return $builder;
    }

    protected function footnoteSubstitutions($footnotes, $year)
    {
        $sub = $this->getVariableSubstitution()->setStudyYear($year);

        $newFootnotes = array();
        foreach ($footnotes as $footnote) {
            $newFootnotes[] = $sub->substitute($footnote);
        }

        return $newFootnotes;
    }

    /**
     * @return \Mrss\Model\ReportItem
     */
    protected function getReportItemModel()
    {
        return $this->getServiceManager()->get('model.reportItem');
    }
}
