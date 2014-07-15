<?php

$fiscalYear = '2012-2013';

$configs = array(
    // Workforce:
    3 => array(
        array(
            'name' => 'Enrollment Data',
            'charts' => array(
                array(
                    'dbColumn' => 'enrollment_information_duplicated_enrollment',
                    'description' => "The headcount of duplicated non-credit workforce development participants for the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'enrollment_information_organizations_served',
                    'description' => "The unduplicated number of organizations for which contract training was provided. (on- or off-campus, online, as distance learning or on the organization's site for $fiscalYear fiscal year)"
                ),
                array(
                    'dbColumn' => 'enrollment_information_training_contracts',
                    'description' => "The total number of training contracts executed in the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'enrollment_information_market_penetration',
                    'description' => "The percentage of organizations served by the workforce training department of the total number of organizations in the college's service area."
                ),
            )
        ),
        array(
            'name' => 'Staffing',
            'charts' => array(
                array(
                    'type' => 'pieChart',
                    'title' => 'Types of Instructors at Your College',
                    'benchmarks' => array(
                        array(
                            'dbColumn' => 'staffing_full_time_instructors',
                            'title' => 'Full-time'
                        ),
                        array(
                            'dbColumn' => 'staffing_part_time_instructors',
                            'title' => 'Part-time'
                        ),
                        array(
                            'dbColumn' => 'staffing_independent_contractors',
                            'title' => 'Contractors'
                        )
                    ),
                    'description' => "The percentages of full-time, part-time and contract instructors that support your college's non-credit workforce training for the $fiscalYear fiscal year."
                ),
                array(
                    'type' => 'pieChart',
                    'title' => 'Types of Instructors National Median',
                    'benchmarks' => array(
                        array(
                            'dbColumn' => 'staffing_full_time_instructors',
                            'title' => 'Full-time',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'staffing_part_time_instructors',
                            'title' => 'Part-time',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'staffing_independent_contractors',
                            'title' => 'Contractors',
                            'median' => true
                        )
                    ),
                    'description' => "The national averages of full-time, part-time and contract instructors that support non-credit workforce training for the $fiscalYear fiscal year."
                )
            )
        ),
        array(
            'name' => 'Retention',
            'charts' => array(
                array(
                    'dbColumn' => 'retention_percent_returning_organizations_served',
                    'description' => "The percentage of organizations that received contract training in the $fiscalYear fiscal year and at least once in a previous time period."
                ),
                array(
                    'dbColumn' => 'retention_percent_returning_students',
                    'description' => "The percentage of students who received workforce training in the $fiscalYear fiscal year and also did so in a previous year."
                ),
            )
        ),
        array(
            'name' => 'Revenue',
            'charts' => array(
                array(
                    'title' => 'Funding Sources at Your College',
                    'type' => 'pieChart',
                    'benchmarks' => array(
                        array(
                            'dbColumn' => 'revenue_earned_revenue',
                            'title' => 'Earned Revenue'
                        ),
                        array(
                            'dbColumn' => 'revenue_grants',
                            'title' => 'Grants'
                        ),
                        array(
                            'dbColumn' => 'revenue_local',
                            'title' => 'Local'
                        ),
                        array(
                            'dbColumn' => 'revenue_state',
                            'title' => 'State'
                        ),
                        array(
                            'dbColumn' => 'revenue_federal',
                            'title' => 'Federal'
                        ),
                    ),
                    'description' => "The percentage of non-credit workforce training gross revenues from continuing education for the $fiscalYear fiscal year."
                ),
                array(
                    'title' => 'Funding Sources National Median',
                    'type' => 'pieChart',
                    'benchmarks' => array(
                        array(
                            'dbColumn' => 'revenue_earned_revenue',
                            'title' => 'Earned Revenue',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'revenue_grants',
                            'title' => 'Grants',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'revenue_local',
                            'title' => 'Local',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'revenue_state',
                            'title' => 'State',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'revenue_federal',
                            'title' => 'Federal',
                            'median' => true
                        ),
                    ),
                    'description' => "The total gross revenue in support of all non-credit workforce training - include all public, grant and earned revenue for the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'revenue_contract_training_percent',
                    'description' => "The percentage of non-credit workforce training revenues by source for your college for the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'revenue_continuing_education_percent',
                    'description' => "The national averages of non-credit workforce training revenues by source for the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'revenue_total',
                    'description' => "The percentage of non-credit workforce training gross revenues from contract training for the $fiscalYear fiscal year."
                )
            )
        ),
        array(
            'name' => 'Expenditures',
            'charts' => array(
                array(
                    'dbColumn' => 'expenditures_contract_training_percent',
                    'description' => "The percentage of total non-credit workforce training instructional and administrative expenses from contract training for the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'expenditures_continuing_education_percent',
                    'description' => "The percentage of total non-credit workforce training instructional and administrative expenses from continuing education for the $fiscalYear fiscal year."
                ),
                array(
                    'title' => 'Expenditures at Your College',
                    'type' => 'pieChart',
                    'benchmarks' => array(
                        array(
                            'dbColumn' => 'expenditures_salaries',
                            'title' => 'Salaries',
                        ),
                        array(
                            'dbColumn' => 'expenditures_benefits',
                            'title' => 'Benefits',
                        ),
                        array(
                            'dbColumn' => 'expenditures_supplies',
                            'title' => 'Supplies',
                        ),
                        array(
                            'dbColumn' => 'expenditures_marketing',
                            'title' => 'Marketing',
                        ),
                        array(
                            'dbColumn' => 'expenditures_capital_equipment',
                            'title' => 'Capital Equipment',
                        ),
                        array(
                            'dbColumn' => 'expenditures_travel',
                            'title' => 'Travel',
                        ),
                    ),
                    'description' => "The percentages of non-credit workforce training instructional and administrative itemized expenses for your college for the $fiscalYear fiscal year."
                ),
                array(
                    'title' => 'Expenditures National Median',
                    'type' => 'pieChart',
                    'benchmarks' => array(
                        array(
                            'dbColumn' => 'expenditures_salaries',
                            'title' => 'Salaries',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'expenditures_benefits',
                            'title' => 'Benefits',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'expenditures_supplies',
                            'title' => 'Supplies',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'expenditures_marketing',
                            'title' => 'Marketing',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'expenditures_capital_equipment',
                            'title' => 'Capital Equipment',
                            'median' => true
                        ),
                        array(
                            'dbColumn' => 'expenditures_travel',
                            'title' => 'Travel',
                            'median' => true
                        ),
                    ),
                    'description' => "The national averages of non-credit workforce training instructional and administrative itemized expenses for the $fiscalYear fiscal year."
                )
            )
        ),
        array(
            'name' => 'Retained Revenue',
            'charts' => array(
                array(
                    'dbColumn' => 'retained_revenue_contract_training',
                    'description' => "Total non-credit workforce training retained revenues from contract training for the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'retained_revenue_total',
                    'description' => "Total non-credit workforce training retained revenues for the $fiscalYear fiscal year."
                ),
                array(
                    'dbColumn' => 'retained_revenue_roi',
                    'description' => "ROI: net revenues by total expenditures"
                ),
            )
        ),
        array(
            'name' => 'Credentials Awarded',
            'charts' => array(
                array(
                    'dbColumn' => 'institutional_demographics_credentials_awarded',
                    'description' => "The number of state, national or industry recognized credentials earned by non-credit workforce training students in the $fiscalYear fiscal year."
                )
            )
        ),
        array(
            'name' => 'Satisfaction',
            'charts' => array(
                array(
                    'dbColumn' => 'satisfaction_client',
                    'description' => "The percentage of very satisfied & satisfied on a 5-point scale of annual contract training clients with training courses/programs for the $fiscalYear fiscal year. "
                ),
                array(
                    'dbColumn' => 'satisfaction_student',
                    'description' => "The percentage of very satisfied & satisfied on a 5-point scale of students in non-credit workforce training courses for the $fiscalYear fiscal year."
                )
            )
        ),
        array(
            'name' => 'Transition from Workforce Training to Credit Coursework',
            'charts' => array(
                array(
                    'dbColumn' => 'transition_students',
                    'description' => "Percentage of non-credit workforce training students that transitioned to one or more credit courses in the $fiscalYear fascal year of completing a non-credit course."
                ),
            )
        )
    )
);

return $configs;
