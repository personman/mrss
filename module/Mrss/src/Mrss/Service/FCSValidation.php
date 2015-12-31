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


    public function validateConversionFactor()
    {
        $conversionFactor = $this->observation->get('institution_conversion_factor');
        if ($conversionFactor) {
            if ($conversionFactor > 1 || $conversionFactor < 0.75) {
                $message = "Your 12- to 9-month conversion factor should fall in the range of 0.75 to 1";
                $code = "conversion_factor";
                $this->addIssue($message, $code, 1);
            }
        }
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

    // Rule 24. Check for cat IV institutions that report data in wrong ranks
    public function validateCategoryIV()
    {
        $categoryToCheck = "Associate's without Ranks";
        $ranksToCheck = array(
            'professor',
            'associate_professor',
            'assistant_professor'
        );

        if ($category = $this->observation->get('institution_aaup_category')) {
            if ($category == $categoryToCheck) {

                $total = 0;
                foreach ($ranksToCheck as $rank) {
                    foreach ($this->getContracts(false) as $contract => $contractLabel) {
                        $total += floatval($this->getFacultyTotalForRankAndContract($rank, $contract));
                    }
                }

                if ($total > 0) {
                    $message = "By definition, data for category IV ($categoryToCheck) institutions must " .
                    "be reported in the No Rank rows and columns.";
                    $code = "cat_4_no_rank";
                    $this->addIssue($message, $code, 2);
                }

            }
        }
    }

    // Rule 50
    public function validateAssociateVsProfessorSalaries()
    {
        $percentDifferenceAllowed = 10;
        $minimumFaculty = 5;

        foreach ($this->getContracts(false) as $contract => $contractLabel) {
            foreach ($this->getGenders() as $gender => $genderLabel) {
                // Make sure we have enough faculty to even validate
                $associateFacultyKey = "ft_{$gender}_associate_professor_number_{$contract}";
                $associateFaculty = $this->observation->get($associateFacultyKey);

                $professorFacultyKey = "ft_{$gender}_professor_number_{$contract}";
                $professorFaculty = $this->observation->get($professorFacultyKey);

                if ($associateFaculty < $minimumFaculty || $professorFaculty < $minimumFaculty) {
                    continue;
                }

                // Look up the average salaries
                $avgAssociateKey = "ft_average_{$gender}_associate_professor_salary_{$contract}";
                $avgAssociateSalary = $this->observation->get($avgAssociateKey);

                $avgProfKey = "ft_average_{$gender}_professor_salary_{$contract}";
                $avgProfSalary = $this->observation->get($avgProfKey);

                // Is the associate average bigger?
                if ($avgAssociateSalary && $avgProfSalary && $avgAssociateSalary > $avgProfSalary) {
                    // By how much?
                    $percentDifference = ($avgAssociateSalary - $avgProfSalary) / $avgProfSalary * 100;

                    if ($percentDifference >= $percentDifferenceAllowed) {
                        $message = "The average salary for Associate ($genderLabel, $contractLabel) " .
                            "is at least {$percentDifferenceAllowed}% greater than the average for Professor.";

                        $code = "associate_vs_professor_{$gender}_{$contract}";
                        $this->addIssue($message, $code, 2);
                    }
                }
            }
        }
    }

    // Rule 51
    public function validateAssistantVsAssociateSalaries()
    {
        $percentDifferenceAllowed = 10;
        $minimumFaculty = 5;

        foreach ($this->getContracts(false) as $contract => $contractLabel) {
            foreach ($this->getGenders() as $gender => $genderLabel) {
                // Make sure we have enough faculty to even validate
                $associateFacultyKey = "ft_{$gender}_associate_professor_number_{$contract}";
                $associateFaculty = $this->observation->get($associateFacultyKey);

                $assistantFacultyKey = "ft_{$gender}_assistant_professor_number_{$contract}";
                $assistantFaculty = $this->observation->get($assistantFacultyKey);

                if ($associateFaculty < $minimumFaculty || $assistantFaculty < $minimumFaculty) {
                    continue;
                }

                // Look up the average salaries
                $avgAssociateKey = "ft_average_{$gender}_associate_professor_salary_{$contract}";
                $avgAssociateSalary = $this->observation->get($avgAssociateKey);

                $avgAssistantKey = "ft_average_{$gender}_assistant_professor_salary_{$contract}";
                $avgAssistantSalary = $this->observation->get($avgAssistantKey);

                // Is the assistant average bigger?
                if ($avgAssociateSalary && $avgAssistantSalary && $avgAssistantSalary > $avgAssociateSalary) {
                    // By how much?
                    $percentDifference = ($avgAssistantSalary - $avgAssociateSalary) / $avgAssociateSalary * 100;

                    if ($percentDifference >= $percentDifferenceAllowed) {
                        $message = "The average salary for Assistant ($genderLabel, $contractLabel) " .
                            "is at least {$percentDifferenceAllowed}% greater than the average for Associate.";

                        $code = "assistant_vs_associate_{$gender}_{$contract}";
                        $this->addIssue($message, $code, 2);
                    }
                }
            }
        }
    }


    // Rules 31-35
    public function validateAverageSalaries()
    {
        $minFaculty = 3;
        $rankMaxes = $this->getAverageSalaryMax();

        if (count($rankMaxes) == 0) {
            return;
        }

        foreach ($this->getContracts(false) as $contract => $contractLabel) {
            foreach ($this->getRanks() as $rank => $rankLabel) {
                foreach ($this->getGenders() as $gender => $genderLabel) {
                    $facultyCountKey = "ft_{$gender}_{$rank}_number_{$contract}";
                    $facultyCount = $this->observation->get($facultyCountKey);

                    if ($facultyCount < $minFaculty) {
                        continue;
                    }

                    // Get the average salary
                    $averageKey = "ft_average_{$gender}_{$rank}_salary_{$contract}";
                    $average = $this->observation->get($averageKey);
                    //pr($rank); pr($gender); pr($average);

                    // Get the max
                    if ($max = $rankMaxes[$rank]) {
                        // Now the comparison
                        if ($average > $max) {
                            $maxFormatted = '$' . number_format($max);
                            $message = "The average salary for $genderLabel $rankLabel $contractLabel is greater than $maxFormatted.";
                            $code = "average_salary_max_{$gender}_{$rank}_{$contract}";
                            $this->addIssue($message, $code, 2);
                        }
                    }

                }
            }

        }
    }

    public function getAverageSalaryMax()
    {
        $maxes = array();

        if ($category = $this->observation->get('institution_aaup_category')) {
            $config = $this->getAverageSalaryMaxConfig();
            if (!empty($config[$category])) {
                $maxes = $config[$category];
            }
        }

        return $maxes;
    }

    public function getAverageSalaryMaxConfig()
    {
        $maxInfo = array(
            // Cat I
            "Doctoral" => array(
                'professor' => 250000,
                'associate_professor' => 175000,
                'assistant_professor' => 150000,
                'instructor' => 100000,
                'lecturer' => 100000,
                'no_rank' => 100000
            ),
            // Cat IIA
            "Master's" => array(
                'professor' => 175000,
                'associate_professor' => 150000,
                'assistant_professor' => 100000,
                'instructor' => 100000,
                'lecturer' => 100000,
                'no_rank' => 100000
            ),
            // Cat IIB
            "Baccalaureate" => array(
                'professor' => 175000,
                'associate_professor' => 150000,
                'assistant_professor' => 100000,
                'instructor' => 150000,
                'lecturer' => 150000,
                'no_rank' => 100000
            ),
            // Cat III
            "Associate's with Ranks" => array(
                'professor' => 150000,
                'associate_professor' => 100000,
                'assistant_professor' => 100000,
                'instructor' => 100000,
                'lecturer' => 100000,
                'no_rank' => 100000
            ),
            // Cat IV
            "Associate's without Ranks" => array(
                'professor' => 100000,
                'associate_professor' => 100000,
                'assistant_professor' => 100000,
                'instructor' => 100000,
                'lecturer' => 100000,
                'no_rank' => 100000
            )
        );

        return $maxInfo;
    }

    // Rule 60 - male vs female salaries
    public function validateMaleVsFemaleSalaries()
    {
        $minFaculty = 5;
        $allowedPercentDifference = 35;

        foreach ($this->getContracts(false) as $contract => $contractLabel) {
            foreach ($this->getRanks() as $rank => $rankLabel) {
                $maleFacultyCountKey = "ft_male_{$rank}_number_{$contract}";
                $maleFacultyCount = $this->observation->get($maleFacultyCountKey);

                $femaleFacultyCountKey = "ft_female_{$rank}_number_{$contract}";
                $femaleFacultyCount = $this->observation->get($femaleFacultyCountKey);

                if ($maleFacultyCount < $minFaculty || $femaleFacultyCount < $minFaculty) {
                    continue;
                }

                // Get the average salaries
                $maleAverageKey = "ft_average_male_{$rank}_salary_{$contract}";
                $maleAverage = $this->observation->get($maleAverageKey);

                $femaleAverageKey = "ft_average_female_{$rank}_salary_{$contract}";
                $femaleAverage = $this->observation->get($femaleAverageKey);

                if ($maleAverage > $femaleAverage) {
                    $larger = $maleAverage;
                    $smaller = $femaleAverage;
                } else {
                    $larger = $femaleAverage;
                    $smaller = $maleAverage;
                }

                // What's the percentage difference?
                $percentDifference = ($larger - $smaller) / $smaller * 100;

                // Is it too big?
                if ($percentDifference > $allowedPercentDifference) {
                    $message = "The average salaries of men and women differ by " .
                        "more than {$allowedPercentDifference}% ($rankLabel $contractLabel).";
                    $code = "avg_salary_men_vs_women_{$rank}_{$contract}";
                    $this->addIssue($message, $code, 2);
                }
            }
        }
    }
    
    // Rule 51 - Just make sure they put SOMETHING in form 3
    public function validateBenefitFormNotSkipped()
    {
        $form3Empty = false;
        $aggregate = $this->observation->get('institution_aggregate_benefits');

        if ($aggregate == 'No') {
            $total = 0;
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getRanks() as $rank => $rankLabel) {
                    foreach ($this->getBenefits() as $benefit => $benefitLabel) {
                        $facultyKey = "ft_{$benefit}_covered_{$rank}_{$contract}";
                        $faculty = $this->observation->get($facultyKey);
                        $total += $faculty;
                    }
                }
            }

            if ($total == 0) {
                $form3Empty = true;
            }

        } elseif ($aggregate == 'Yes') {
            $total = 0;
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getBenefits() as $benefit => $benefitLabel) {
                    $facultyKey = "ft_{$benefit}_covered_no_diff_{$contract}";
                    $faculty = $this->observation->get($facultyKey);
                    $total += $faculty;
                }
            }

            if ($total == 0) {
                $form3Empty = true;
            }
        }

        if ($form3Empty) {
            $message = "There are no data for Form 3: Benefits";
            $code = "benefits_form_empty";
            $this->addIssue($message, $code, 3);
        }
    }

    public function validateBenefitPercentages()
    {
        $benefitsToCheck = array(
            'retirement' => 50,
            'medical' => 50,
            'combined_medical_dental' => 50,
            'tuition' => 50 // The tuition check should be more than
        );

        // Only check this if they don't aggregate their benefits
        if ($this->observation->get('institution_aggregate_benefits') == 'No') {
            foreach ($benefitsToCheck as $benefit => $minPercentage) {
                foreach ($this->getContracts(false) as $contract => $contractLabel) {
                    foreach ($this->getRanks() as $rank => $rankLabel) {
                        // First, sum up the male and female counts from form 2
                        $form2Total = $this->getFacultyTotalForRankAndContract($rank, $contract);

                        // Have they reported faculty count on form 2 yet?
                        if (!$form2Total) {
                            continue;
                        }

                        // Now how many of those folks get the benefit?
                        $benefitCol = "ft_{$benefit}_covered_{$rank}_{$contract}";
                        $benefitCount = $this->observation->get($benefitCol);

                        // Skip if they haven't reported it yet
                        if (!$benefitCount) {
                            continue;
                        }

                        // So, what percentage is that?
                        $percentage = ($benefitCount / $form2Total) * 100;

                        // Does that trigger an issue?
                        if ($benefit == 'tuition') {
                            $hasIssue = ($percentage > $minPercentage);
                            $desc = 'more than';
                        } else {
                            $hasIssue = ($percentage < $minPercentage);
                            $desc = 'less than';
                        }

                        if ($hasIssue) {
                            $benefitLabel = $this->getBenefitLabel($benefit);
                            $message = "Number covered for $benefitLabel is $desc {$minPercentage}% " .
                                "of the similar number of faculty in form 2 ({$contractLabel} {$rankLabel}).";
                            $code = "{ft_benefit_min_{$benefit}_{$rank}_{$contract}";
                            $this->addIssue($message, $code, 3);
                        }
                    }
                }
            }
        }
    }

    protected function getBenefitLabel($benefit)
    {
        return ucwords(str_replace('_', ' ', $benefit));
    }

    public function validateBenefitsVsSalaries()
    {
        $minPercent = 5;
        $maxPercent = 50;

        if ($this->observation->get('institution_aggregate_benefits') == 'No') {
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getRanks() as $rank => $rankLabel) {
                    // Total salaries
                    $salaryTotal = $this->getSalaryTotalForRankAndContract($rank, $contract);

                    // Get the total cost of benefits
                    $benefitTotalCol = "ft_total_benefits_expenditure_{$rank}_{$contract}";
                    $benefitTotal = $this->observation->get($benefitTotalCol);


                    if ($salaryTotal && $benefitTotal) {
                        $benefitsAsPercentOfSalary = ($benefitTotal / $salaryTotal) * 100;

                        // Lower than min?
                        if ($benefitsAsPercentOfSalary < $minPercent) {
                            $message = "Total benefit expenditure is less than $minPercent% for $rankLabel $contractLabel.";
                            $code = "total_benefit_exp_min_perc_salaries_{$rank}_{$contract}";
                            $this->addIssue($message, $code, 3);
                        }

                        // Higher than max?
                        if ($benefitsAsPercentOfSalary > $maxPercent) {
                            $message = "Total benefit expenditure is greater than $maxPercent% for $rankLabel $contractLabel.";
                            $code = "total_benefit_exp_max_perc_salaries_{$rank}_{$contract}";
                            $this->addIssue($message, $code, 3);
                        }
                    }
                }
            }
        }
    }

    // Rule 112
    public function validateBenefitNumberCovered()
    {
        foreach ($this->getBenefits() as $benefit => $benefitLabel) {
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getRanks() as $rank => $rankLabel) {
                    // First, sum up the male and female counts from form 2
                    $form2Total = $this->getFacultyTotalForRankAndContract($rank, $contract);

                    // Have they reported faculty count on form 2 yet?
                    if (!$form2Total) {
                        continue;
                    }

                    // Now how many of those folks get the benefit?
                    $benefitCol = "ft_{$benefit}_covered_{$rank}_{$contract}";
                    $benefitCount = $this->observation->get($benefitCol);

                    // Skip if they haven't reported it yet
                    if (!$benefitCount) {
                        continue;
                    }

                    // Check to see if the number covered is greater than the number with salary
                    if ($benefitCount > $form2Total) {
                        $message = "The number covered for $benefitLabel is greater than the total number of faculty in Form 2: $rankLabel, $contractLabel";
                        $code = "number_covered_greater_{$benefit}_{$rank}_{$contract}";
                        $this->addIssue($message, $code, 3);
                    }
                }
            }
        }
    }


    // Rule 121, 123-124: Make sure some data are reported for medical or combined
    public function validateMedicalBenefit()
    {
        $medicalTotal = 0;
        $combinedTotal = 0;
        $retirementTotal = 0;

        $expenditure = 'expenditure';

        $form = 3;

        if ($this->observation->get('institution_aggregate_benefits') == 'No') {
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getRanks() as $rank => $rankLabel) {
                    $medicalKey = "ft_medical_{$expenditure}_{$rank}_{$contract}";
                    $retirementKey = "ft_retirement_{$expenditure}_{$rank}_{$contract}";

                    $shortRank = $this->shortenRank($rank);
                    $combinedKey = "ft_combined_medical_dental_{$expenditure}_{$shortRank}_{$contract}";

                    $medical = $this->observation->get($medicalKey);
                    $medicalTotal += floatval($medical);

                    $combined = $this->observation->get($combinedKey);
                    $combinedTotal += floatval($combined);

                    $retirement = $this->observation->get($retirementKey);
                    $retirementTotal += floatval($retirement);
                }
            }
        } else {
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                $rank = 'no_diff';
                $expenditure = 'expentirue';
                $medicalKey = "ft_medical_{$expenditure}_{$rank}_{$contract}";
                $retirementKey = "ft_retirement_{$expenditure}_{$rank}_{$contract}";

                $shortRank = $this->shortenRank($rank);
                $combinedKey = "ft_combined_medical_dental_{$expenditure}_{$shortRank}_{$contract}";

                $medical = $this->observation->get($medicalKey);
                $medicalTotal += floatval($medical);

                $combined = $this->observation->get($combinedKey);
                $combinedTotal += floatval($combined);

                $retirement = $this->observation->get($retirementKey);
                $retirementTotal += floatval($retirement);
            }
        }

        // First, check to see if they're both empty
        if ($medicalTotal + $combinedTotal == 0) {
            $message = "There are no data reported for Medical benefit.";
            $code = "no_medical";
            $this->addIssue($message, $code, $form);
        }

        // Now check to see if they're both populated
        if ($medicalTotal && $combinedTotal) {
            $message = "Data are reported for both Medical and Combined Medical-Dental benefit items. " .
                "Only one of these should be reported; please see instructions.";
            $code = "both_medical";
            $this->addIssue($message, $code, $form);
        }

        // Check retirement
        if ($retirementTotal == 0) {
            $message = "There are no data reported for Retirement benefit.";
            $code = "no_retirement";
            $this->addIssue($message, $code, $form);
        }
    }

    // Rules 140-146
    public function validateSpecificBenefitCosts()
    {
        // If the rank/contract has fewer than x faculty, skip this validation rule
        $facultyCountMin = 10;

        $benefitRules = array(
            'retirement' => array(
                'min' => 5,
                'max' => 25
            ),
            'medical' => array(
                'max' => 30,
            ),
            'combined_medical_dental' => array(
                'max' => 30,
            ),
            'tuition' => array(
                'max' => 25
            ),
            'worker_comp' => array(
                'max' => 2
            ),
            'unemployment' => array(
                'max' => 3
            ),
            'fica' => array(
                'max' => 7.65
            )
        );

        if ($this->observation->get('institution_aggregate_benefits') == 'No') {
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getRanks() as $rank => $rankLabel) {

                    $salaryTotal = $this->getSalaryTotalForRankAndContract($rank, $contract);
                    $facultyCount = $this->getFacultyTotalForRankAndContract($rank, $contract);

                    if ($facultyCount < $facultyCountMin || !$salaryTotal) {
                        continue;
                    }


                    foreach ($benefitRules as $benefit => $rules) {
                        // Handle long dbColumns
                        if ($benefit == 'combined_medical_dental') {
                            $shortenedRank = $this->shortenRank($rank);
                        } else {
                            $shortenedRank = $rank;
                        }

                        $benefitCostKey = "ft_{$benefit}_expenditure_{$shortenedRank}_{$contract}";
                        $benefitCost = $this->observation->get($benefitCostKey);
                        $benefitLabel = $this->getBenefitLabel($benefit);

                        $benefitPercent = ($benefitCost / $salaryTotal) * 100;

                        // Compare to min
                        if (!empty($rules['min']) && $benefitPercent < $rules['min']) {
                            $min = $rules['min'];
                            $message = "Expenditure for $benefitLabel is less than $min% of salary for $rankLabel $contractLabel.";
                            $code = "{$benefit}_expenditure_min_{$rank}_{$contract}";
                            $this->addIssue($message, $code, 3);
                        }

                        // Compare to max
                        if (!empty($rules['max']) && $benefitPercent > $rules['max']) {
                            $max = $rules['max'];
                            $message = "Expenditure for $benefitLabel is more than $max% of salary for $rankLabel $contractLabel.";
                            $code = "{$benefit}_expenditure_max_{$rank}_{$contract}";
                            $this->addIssue($message, $code, 3);
                        }
                    }
                }
            }
        }
    }

    // Rule 150-151
    public function validateBenefitCountAndDollars()
    {
        $form = 3;

        if ($this->observation->get('institution_aggregate_benefits') == 'No') {
            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getRanks() as $rank => $rankLabel) {
                    foreach ($this->getBenefits() as $benefit => $benefitLabel) {
                        $facultyKey = "ft_{$benefit}_covered_{$rank}_{$contract}";
                        $faculty = floatval($this->observation->get($facultyKey));

                        if ($benefit == 'combined_medical_dental') {
                            $shortenedRank = $this->shortenRank($rank);
                        } else {
                            $shortenedRank = $rank;
                        }

                        $dollarKey = "ft_{$benefit}_expenditure_{$shortenedRank}_{$contract}";
                        $dollars = floatval($this->observation->get($dollarKey));

                        // Now see if one is filled out and the other is missing
                        if ($faculty > 0 && $dollars == 0) {
                            $message = "Expenditure for {$benefitLabel} is 0, but the number covered is greater " .
                                "than 0 ($rankLabel, $contractLabel).";
                            $code = "faculty_no_expenditure_{$benefit}_{$rank}_{$contract}";
                            $this->addIssue($message, $code, $form);
                        }

                        // Next check the opposite case
                        if ($dollars > 0 && $faculty == 0) {
                            $message = "Expenditure for {$benefitLabel} is greater than 0, but the number " .
                                "covered is 0 ($rankLabel, $contractLabel).";
                            $code = "expenditure_no_faculty_{$benefit}_{$rank}_{$contract}";
                            $this->addIssue($message, $code, $form);
                        }
                    }
                }
            }
        } else {
            $rank = 'no_diff';
            $rankLabel = "Undifferentiated Rank";

            foreach ($this->getContracts(false) as $contract => $contractLabel) {
                foreach ($this->getBenefits() as $benefit => $benefitLabel) {
                    $facultyKey = "ft_{$benefit}_covered_{$rank}_{$contract}";
                    $faculty = floatval($this->observation->get($facultyKey));

                    // Living with this misspelling:
                    $dollarKey = "ft_{$benefit}_expentirue_{$rank}_{$contract}";
                    $dollars = floatval($this->observation->get($dollarKey));

                    // Now see if one is filled out and the other is missing
                    if ($faculty > 0 && $dollars == 0) {
                        $message = "Expenditure for {$benefitLabel} is 0, but the number covered is greater " .
                            "than 0 ($rankLabel, $contractLabel).";
                        $code = "faculty_no_expenditure_{$benefit}_{$rank}_{$contract}";
                        $this->addIssue($message, $code, $form);
                    }

                    // Next check the opposite case
                    if ($dollars > 0 && $faculty == 0) {
                        $message = "Expenditure for {$benefitLabel} is greater than 0, but the number " .
                            "covered is 0 ($rankLabel, $contractLabel).";
                        $code = "expenditure_no_faculty_{$benefit}_{$rank}_{$contract}";
                        $this->addIssue($message, $code, $form);
                    }
                }
            }
        }
    }

    // Rule 200, 210
    public function validateContinuingFaculty()
    {
        $total = 0;

        foreach ($this->getContracts() as $contract => $contractLabel) {
            foreach ($this->getRanks() as $rank => $rankLabel) {
                $faculty = floatval($this->observation->get("ft_number_continuing_{$rank}_{$contract}"));
                $current = floatval($this->observation->get("ft_current_salary_{$rank}_{$contract}"));
                $previous = floatval($this->observation->get("ft_previous_salary_{$rank}_{$contract}"));

                $rankTotal = $faculty + $current + $previous;
                $total += $rankTotal;

                // Check for partial data
                if ($rankTotal > 0 && ($faculty == 0 || $current == 0 || $previous == 0)) {
                    $message = "At least one column is missing for $rankLabel, $contractLabel.";
                    $code = "continuing_missing_{$rank}_{$contract}";
                    $this->addIssue($message, $code, 4);
                }
            }
        }

        if ($total == 0) {
            $message = "There are no data for form 4: Full-time Continuing Faculty Salaries.";
            $code = "no_continuing_salaries";
            $this->addIssue($message, $code, 4);
        }
    }

    protected function shortenRank($rank)
    {
        return str_replace(
            array('associate_professor', 'assistant_professor'),
            array('associate_prof', 'assistant_prof'),
            $rank
        );
    }

    protected function getSalaryTotalForRankAndContract($rank, $contract)
    {
        $maleCol = "ft_male_{$rank}_salaries_{$contract}";
        $femaleCol = "ft_female_{$rank}_salaries_{$contract}";
        $form2Total = $this->observation->get($maleCol) + $this->observation->get($femaleCol);

        return $form2Total;
    }

    protected function getFacultyTotalForRankAndContract($rank, $contract)
    {
        $maleCol = "ft_male_{$rank}_number_{$contract}";
        $femaleCol = "ft_female_{$rank}_number_{$contract}";
        $form2Total = $this->observation->get($maleCol) + $this->observation->get($femaleCol);

        return $form2Total;
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

    // Rule 400
    public function validateExecNotEmpty()
    {
        $fields = array(
            'ft_president_salary',
            'ft_president_supplemental',
            'ft_chief_academic_salary',
            'ft_chief_academic_supplemental',
            'ft_chief_financial_salary',
            'ft_chief_financial_supplemental',
            'ft_chief_development_salary',
            'ft_chief_development_supplemental',
            'ft_chief_administrative_salary',
            'ft_chief_administrative_supplemental',
            'ft_chief_counsel_salary',
            'ft_chief_counsel_supplemental',
            'ft_director_enrollment_management_salary',
            'ft_director_enrollment_management_supplemental',
            'ft_director_athletics_salary',
            'ft_director_athletics_supplemental',
        );

        $total = 0;
        foreach ($fields as $field) {
            $total += floatval($this->observation->get($field));
        }

        if ($total == 0) {
            $message = "There are no data for Form 5: Administrative Compensation.";
            $code = "admin_comp_empty";
            $this->addIssue($message, $code, 5);
        }
    }

    // Rules 410-417
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

    protected function getBenefits()
    {
        $benefits = array(
            'retirement' => 'Retirement',
            'medical' => 'Medical',
            'combined_medical_dental' => 'Combined Medical w/ Dental',
            'tuition' => 'Tuition',
            'fica' => 'FICA',
            'unemployment' => 'Unemployment',
            'group_life' => 'Group Life',
            'worker_comp' => "Worker's Comp",
            'other' => 'Other'
        );

        return $benefits;
    }
}
