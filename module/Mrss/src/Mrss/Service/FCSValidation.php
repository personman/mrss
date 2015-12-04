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

    /**
     * Form 2
     * Check to see if they entered male numbers but left all female fields blank
     */
    public function validateSalariesWomen()
    {
        // Have they entered male fields?
        $maleTotal = 0;
        foreach ($this->getForm2Fields() as $field) {
            $maleTotal += $this->observation->get($field);
        }

        // Have they entered female fields?
        $femaleTotal = 0;
        foreach ($this->getForm2Fields('female') as $field) {
            $femaleTotal += $this->observation->get($field);
        }

        // Create an issue if male numbers are present and female is all empty
        if ($maleTotal && !$femaleTotal) {
            $this->addIssue(
                "Form 2 values have been entered for men, but not for women.",
                'salaries_women_empty',
                2
            );
        }
    }
    
    protected function getForm2Fields($gender = 'male')
    {
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
            'ft_male_professor_number_12_month',
            'ft_male_professor_salaries_12_month',
            'ft_male_associate_professor_number_12_month',
            'ft_male_associate_professor_salaries_12_month',
            'ft_male_assistant_professor_number_12_month',
            'ft_male_assistant_professor_salaries_12_month',
            'ft_male_instructor_number_12_month',
            'ft_male_instructor_salaries_12_month',
            'ft_male_lecturer_number_12_month',
            'ft_male_lecturer_salaries_12_month',
            'ft_male_no_rank_number_12_month',
            'ft_male_no_rank_salaries_12_month',
        );

        if ($gender == 'female') {
            $newFields = array();
            foreach ($salaryFields as $field) {
                $newFields[] = str_replace('male', 'female', $field);
            }

            $salaryFields = $newFields;
        }

        return $salaryFields;
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

    public function validateSalaryIncrease()
    {
        $maxIncrease = 15;

        foreach ($this->getContracts() as $contract => $contractLabel) {
            foreach ($this->getRanks() as $rank => $rankLabel) {
                // Only check top 3 ranks
                if (!in_array($rank, array('professor', 'associate', 'assistant'))) {
                    continue;
                }

                // Account for some naming oddities
                if ($rank != 'professor') {
                    $rank .= '_professor';
                }

                $percentChangeCol = "ft_percent_change_{$rank}_{$contract}";

                $change = $this->observation->get($percentChangeCol);

                if ($change > $maxIncrease) {
                    $this->addIssue(
                        "There is an increase of more than $maxIncrease% for $contractLabel $rankLabel salaries.",
                        "salary_increase_{$contract}_{$rank}",
                        4
                    );
                }

                // Also create an issue if salaries decreased
                if ($change < 0) {
                    $this->addIssue(
                        "There is a decrease for $contractLabel $rankLabel salaries.",
                        "salary_decrease_{$contract}_{$rank}",
                        4
                    );

                }

            }
        }
    }

    protected function getContracts()
    {
        return array(
            'standard' => '9-Month',
            '12_month' => '12-Month'
        );
    }

    protected function getRanks() {
        return array(
            'professor' => 'Professor',
            'associate' => 'Associate',
            'assistant' => 'Assistant',
            'instructor' => 'Instructor',
            'lecturer' => 'Lecturer',
            'no_rank' => 'No Rank'
        );
    }
}
