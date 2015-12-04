<?php

namespace Mrss\Service;

use Mrss\Entity\Observation;

class FCSValidation
{
    /** @var  Observation $observation */
    protected $observation;

    protected $priorYearObservation;

    protected $issues = array();

    public function setObservation(Observation $observation)
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

        $salaryFields = $this->getForm2Fields();
        $salaryFields = array_merge($salaryFields, $this->getForm2Fields('female'));

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

    public function validateForm2FacultyTotals()
    {
        $colsToSum = array('ntt', 'tt', 't');

        foreach ($this->getGenders() as $gender => $genderLabel) {
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getRanks() as $rank => $rankLabel) {
                    $totalCol = "ft_{$gender}_{$rank}_number_{$contract}";
                    $total = $this->observation->get($totalCol);
                    $sum = 0;

                    foreach ($colsToSum as $col) {
                        $colToSum = "ft_{$gender}_{$rank}_{$col}_{$contract}";
                        $sum += $this->observation->get($colToSum);
                    }

                    if ($total && $total != $sum) {
                        $message = "The number of faculty entered for $genderLabel $contractLabel $rankLabel does not equal the sum of the non-tenure track, on tenure-track, and tenured fields.";
                        $code = "ft_faculty_sum_mismatch_{$gender}_{$rank}_{$contract}";
                        $this->addIssue($message, $code, 2);
                    }
                }
            }
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
        if ($total && $total > $max) {
            $this->addIssue("Total compensation for President/Chancellor is greater than $$formattedMax.", $code . '_max', 5);
        }

        // Too low?
        if ($total && $total < $min) {
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

    protected function getContracts($nineMonthAsStandard = true)
    {
        $nineMonth = '9_month';
        if ($nineMonthAsStandard) {
            $nineMonth = 'standard';
        }

        return array(
            $nineMonth => '9-Month',
            '12_month' => '12-Month'
        );
    }

    protected function getRanks() {
        return array(
            'professor' => 'Professor',
            'associate_professor' => 'Associate',
            'assistant_professor' => 'Assistant',
            'instructor' => 'Instructor',
            'lecturer' => 'Lecturer',
            'no_rank' => 'No Rank'
        );
    }

    protected function getGenders()
    {
        return array(
            'male' => 'Male',
            'female' => 'Female'
        );
    }
}
