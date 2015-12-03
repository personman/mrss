<?php

namespace Mrss\Service;

class FCSValidation
{
    protected $observation;

    protected $priorYearObservation;

    protected $issues = array();

    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    public function runValidation($observation, $priorYearObservation = null)
    {
        $this->setObservation($observation);
        $this->priorYearObservation = $priorYearObservation;

        foreach ($this->getMethodNames() as $method) {
            $this->$method();
        }

        return $this->getIssues();
    }

    public function getMethodNames()
    {
        $prefix = 'validate';
        $names = array();

        foreach (get_class_methods($this) as $methodName) {
            if (substr($methodName, 0, strlen($prefix)) == $prefix) {
                $names[] = $methodName;
            }
        }

        return $names;
    }

    /**
     * @todo: May need a unique id per message/variable so user resolved issues don't get overwritten
     *
     * @param $message
     */
    protected function addIssue($message, $errorCode, $formUrl = null)
    {
        $this->issues[] = array(
            'message' => $message,
            'formUrl' => $formUrl,
            'errorCode' => $errorCode
        );
    }

    public function getIssues()
    {
        return $this->issues;
    }

    public function validateSalariesEntered()
    {
        $code = 'salaries_entered';

        // @todo: add more
        $salaryFields = array(
            'ft_male_professor_number_9_month',
            'ft_male_professor_salaries_9_month',
            'ft_male_associate_professor_number_9_month',
            'ft_male_associate_professor_salaries_9_month',
            'ft_male_assistant_professor_number_9_month',
            'ft_male_assistant_professor_salaries_9_month',
            'ft_male_instructor_number_9_month',
            'ft_male_instructor_salaries_9_month',
            'ft_male_lecturer_number_9_month',
            'ft_male_lecturer_salaries_9_month',
            'ft_male_no_rank_number_9_month',
            'ft_male_no_rank_salaries_9_month',
        );

        $total = 0;
        foreach ($salaryFields as $dbColumn) {
            $total += $this->observation->get($dbColumn);
        }

        if ($total == 0) {
            $this->addIssue("There are no data for Form 2: Full-time Faculty Salary.", $code, 2);
        }
    }

    public function validateExecCompensation()
    {
        $code = 'exec_compensation';

        $execSalary = $this->observation->get('ft_president_salary');
        $execSuppl = $this->observation->get('ft_president_supplemental');
        $total = $execSalary + $execSuppl;

        $max = 1000000;
        $min = 50000;

        $formattedMax = number_format($max);
        $formattedMin = number_format($min);

        if ($total == 0) {
            //return;
        }

        // Too high?
        if ($total > $max) {
            $this->addIssue("Total compensation for President/Chancellor is greater than $$formattedMax.", $code . '_max', 5);
        }

        // Too low?
        if ($total < $min) {
            $this->addIssue("Total compensation for President/Chancellor is less than $$formattedMin. Please verify that this is an annual amount.", $code . '_min', 5);
        }
    }
}
