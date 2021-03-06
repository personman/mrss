<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;

class NCCBPValidation extends AbstractValidation
{
    public function validateAutomatic()
    {
        // Everyone gets these issues, if they've filled them out
        if (null !== $this->observation->get('pell_grant_eligble')) {
            $message = "For Pell Grant Eligible Students, include both those that did receive them and those " .
                "that did not receive them because of administrative or other hurdles.";
            $code = "auto_pell";
            $form = '1';
            $this->addIssue($message, $code, $form);
        }

        // Disabled. Caused confusion
        if (false &&null !== $this->observation->get('dollars_tuition_fees_sour')) {
            $message = 'Under revenue sources, include all revenue, even if it is labeled as “non-operating” ' .
                'on your financial statements.';
            $code = "auto_revenue_sources";
            $form = '21';
            $this->addIssue($message, $code, $form);
        }
    }

    public function validateZeros()
    {
        foreach ($this->getBenchmarksThatShouldNotBeZero() as $benchmark) {
            $value = $this->observation->get($benchmark->getDbColumn());

            $col = $benchmark->getDbColumn();

            if ($value === 0.0 || $value === '0') {
                $label = $benchmark->getDescriptiveReportLabel();
                $this->addIssue(
                    'Unexpected zero: ' . $label . '. If none or not offered at your institution, please leave blank.',
                    'zero_' . $col,
                    $benchmark->getBenchmarkGroup()->getUrl()
                );
            }
        }

        //$this->addIssue('Unexpected zero', 'zero', 2);
    }

    public function validateTotals()
    {
        foreach ($this->getTotalsConfig() as $config) {
            $totalCol = $config[0];
            $columns = $config[1];

            $total = $this->observation->get($totalCol);

            $sum = 0;
            foreach ($columns as $column) {
                $sum += $this->observation->get($column);
            }

            if ($total && $sum && round($total) != round($sum)) {
                $totalBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($totalCol);
                $name = $totalBenchmark->getDescriptiveReportLabel();
                $message = "The sum of the preceding fields does not match the $name.";
                $code = "total_mismatch_$totalCol";
                $formUrl = $totalBenchmark->getBenchmarkGroup()->getUrl();

                $this->addIssue($message, $code, $formUrl);
            }
        }
    }

    protected function getTotalsConfig()
    {
        return array(
            array(
                // Total:
                'revenue_total',
                // Parts:
                array(
                    'revenue_ll',
                    'revenue_continuing_education',
                    'revenue_abe',
                    'revenue_contract_training',
                    'revenue_other'
                )
            ),
            array(
                // Total:
                'expenditures_total',
                // Parts:
                array(
                    'expenditures_ll',
                    'expenditures_continuing_education',
                    'expenditures_abe',
                    'expenditures_contract_training',
                    'expenditures_other'
                )
            ),
            array(
                // Total:
                'tot_operate_rev',
                // Parts:
                array(
                    'dollars_tuition_fees_sour',
                    'dollars_loc_sour',
                    'dollars_state_sour',
                    'private_sour',
                    'dollars_sales_sour',
                    'dollars_other_sour'
                )
            ),
        );
    }

    public function validatePassingGrades()
    {
        foreach ($this->getPassingConfig() as $config) {
            $passing = $config[0];
            $toCheck = $config[1];

            $allOthers = true;
            foreach ($toCheck as $dbColumn) {
                if (null === $this->observation->get($dbColumn)) {
                    $allOthers = false;
                }
            }

            if ($allOthers) {
                if (null === $this->observation->get($passing)) {
                    $passMark = $this->getBenchmarkModel()->findOneByDbColumn($passing);
                    $formUrl = $passMark->getBenchmarkGroup()->getUrl();
                    $code = 'passing_missing_' . $passing;

                    $message = "If your institution does not use passing grades, enter a zero for " .
                        $passMark->getDescriptiveReportLabel() . '.';

                    $this->addIssue($message, $code, $formUrl);
                }
            }
        }
    }

    protected function getPassingConfig()
    {
        return array(
            array('p', array('a', 'b', 'c', 'd', 'f', 'w', 'p')),
            array('form17b_dist_learn_grad_p', array(
                'form17b_dist_learn_grad_a',
                'form17b_dist_learn_grad_b',
                'form17b_dist_learn_grad_c',
                'form17b_dist_learn_grad_p',
                'form17b_dist_learn_grad_d',
                'form17b_dist_learn_grad_f',
                'form17b_dist_learn_grad_w'
            ))
        );
    }

    public function validatePairs()
    {
        foreach ($this->getPairs() as $pair) {
            $value1 = $this->observation->get($pair[0]);
            $value2 = $this->observation->get($pair[1]);

            if ((is_null($value1) && !is_null($value2)) || (!is_null($value1) && is_null($value2))) {
                $benchmark1 = $this->getBenchmarkModel()->findOneByDbColumn($pair[0]);
                $benchmark2 = $this->getBenchmarkModel()->findOneByDbColumn($pair[1]);
                $formUrl = $benchmark1->getBenchmarkGroup()->getUrl();

                $message = "You have entered data for one of the following measures, but not the other. " .
                    "Please enter data for both: " . $benchmark1->getDescriptiveReportLabel() . ', ' .
                    $benchmark2->getDescriptiveReportLabel();
                $code = 'pair_' . $pair[0] . '_' . $pair[1];

                $this->addIssue($message, $code, $formUrl);
            }
        }
    }

    protected function getPairs()
    {
        return array(
            array(
                'ft_f_yminus3_degr_not_transf',
                'ft_f_yminus3_degr_and_transf'
            ),
            array(
                'ft_f_yminus4_degr_not_transf',
                'ft_f_yminus4_degr_and_transf'
            ),
            array(
                'pt_f_yminus4_degr_not_transf',
                'pt_f_yminus4_degr_and_transf'
            ),
            array(
                'ft_f_yminus7_degr_not_transf',
                'ft_yminus7_degr_and_tranf'
            ),
            array(
                'pt_f_yminus7_degr_not_transf',
                'pt_yminus7_degr_and_tranf'
            ),
        );
    }

    /**
     * @return Benchmark[]
     */
    protected function getBenchmarksThatShouldNotBeZero()
    {
        $model = $this->getBenchmarkModel();
        $formUrlsToSkip = array('12', '17', 'NC3', 'NC5', 'NC6');

        $benchmarks = array();
        foreach ($model->findAll() as $benchmark) {
            if (!$benchmark->getComputed()) {
                if (!in_array($benchmark->getBenchmarkGroup()->getUrl(), $formUrlsToSkip)) {
                    $benchmarks[] = $benchmark;
                }
            }
        }

        return $benchmarks;
    }
}
