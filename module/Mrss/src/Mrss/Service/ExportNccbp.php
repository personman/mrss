<?php

namespace Mrss\Service;

use Zend\Db\Sql\Sql;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;

/**
 * @deprecated
 * Class ExportNccbp
 *
 * Connect to the NCCBP database and export the data directly to an excel file
 * @package Mrss\Service
 */
class ExportNccbp
{
    /**
     * @var PHPExcel
     */
    protected $excel;
    protected $filename = 'nccbp-data';
    protected $benchmarks = array();
    protected $colleges = array();
    protected $individualTableValues = array();

    /**
     * The nccbp db using zend db
     * @var
     */
    protected $dbAdapter;

    /**
     * Constructor
     *
     * @param \Zend\Db\Adapter\Adapter $dbAdapter
     */
    public function __construct($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }


    public function export()
    {
        // This may take some time (and RAM)
        takeYourTime();

        // Start building the Excel file
        $this->excel = new PHPExcel();

        foreach ($this->getYears() as $year) {
            $this->addSheetForYear($year);
        }

        // Remove the default sheet
        $sheetIndex = $this->excel->getIndex(
            $this->excel->getSheetByName('Worksheet')
        );
        $this->excel->removeSheetByIndex($sheetIndex);

        $this->download();
    }

    public function getYears()
    {
        $years = range(2007, date('Y'));

        return $years;
    }

    protected function addSheetForYear($year)
    {
        $this->colleges = array();

        // Create the sheet
        $sheet = new PHPExcel_Worksheet($this->excel, "$year");
        $this->excel->addSheet($sheet);
        $this->excel->setActiveSheetIndexByName("$year");

        // Add the headers
        $this->addHeaders();

        // Populate the values
        $this->addValues($year);
    }


    protected function addHeaders()
    {
        $sheet = $this->excel->getActiveSheet();

        $row = 1;

        // Add static headers for first two columns
        $sheet->setCellValueByColumnAndRow(
            0,
            $row,
            'Institution'
        );
        $sheet->getColumnDimensionByColumn(0)->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(
            1,
            $row,
            'IPEDS ID'
        );
        $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);


        $benchmarks = $this->getBenchmarks();
        foreach ($benchmarks as $benchmark) {
            $column = $benchmark['column'];

            // The benchmark label
            $sheet->setCellValueByColumnAndRow(
                $column,
                $row,
                $benchmark['label']
            );

            // The db field name
            $sheet->setCellValueByColumnAndRow(
                $column,
                $row + 1,
                $benchmark['field_name']
            );

            // The form name
            $sheet->setCellValueByColumnAndRow(
                $column,
                $row + 2,
                $benchmark['formLabel']
            );



            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }
    }

    protected function addValues($year)
    {
        foreach ($this->getTables() as $table) {
            $query = "select n.title, form.*
from $table form
inner join node n on n.nid = form.nid
inner join content_field_data_entry_year y on y.nid = n.nid
where y.field_data_entry_year_value = '$year'
ORDER BY n.title";

            $statement = $this->dbAdapter->query($query);
            $result = $statement->execute();

            $result = $this->addFieldsFromIndividualTables($result, $table);

            foreach ($result as $row) {
                $this->placeRowInSheet($row);
            }
        }
    }

    /**
     * A couple of NCCBP fields are stored in their own db tables.
     * Look them up separately and add them to the results.
     *
     * @param $result
     * @param $table
     * @return array
     */
    protected function addFieldsFromIndividualTables($result, $table)
    {
        $tablesWithThisIssue = array(
            'content_type_group_form10_career_comp' => array(
                'field_10_empl_satis_prep'
            ),
            'content_type_group_form11_ret_succ_core' => array(
                'field_11_al_abcp'
            )
        );

        if (in_array($table, array_keys($tablesWithThisIssue))) {
            $fields = $tablesWithThisIssue[$table];

            foreach ($fields as $field) {
                $keyedValues = $this->getKeyedIndividualTableValues(
                    $field
                );

                // Now put it in the results
                $newResults = array();
                foreach ($result as $row) {
                    if (!empty($keyedValues[$row['nid']])) {
                        $value = $keyedValues[$row['nid']];
                        $row[$field . '_value'] = $value;
                    }

                    $newResults[] = $row;
                }
            }

            $result = $newResults;
        }

        return $result;
    }

    protected function getKeyedIndividualTableValues($field)
    {
        if (empty($this->individualTableValues[$field])) {
            $individualTable = 'content_' . $field;
            $individualTableField = $field . '_value';

            $query = "select nid, $individualTableField value
                from $individualTable
                where $individualTableField IS NOT NULL";

            $statement = $this->dbAdapter->query($query);
            $individualResults = $statement->execute();

            $keyedValues = array();
            foreach ($individualResults as $row) {
                $keyedValues[$row['nid']] = $row['value'];
            }

            $this->individualTableValues[$field] = $keyedValues;
        }

        return $this->individualTableValues[$field];
    }

    protected function placeRowInSheet($row)
    {
        $sheet = $this->excel->getActiveSheet();

        $college = $this->extractCollegeInfo($row);
        if (empty($this->colleges[$college['ipeds']])) {
            $college['row'] = count($this->colleges) + 4;
            $this->colleges[$college['ipeds']] = $college;

            // place college name and ipeds
            $sheet = $this->excel->getActiveSheet();
            $sheet->setCellValueByColumnAndRow(
                0,
                $college['row'],
                $college['name']
            );
            $sheet->setCellValueByColumnAndRow(
                1,
                $college['row'],
                $college['ipeds']
            );
        }

        $college = $this->colleges[$college['ipeds']];
        $rowNum = $college['row'];

        // Now the actual data
        foreach ($row as $key => $value) {
            if ($column = $this->getColumnForField($key)) {
                $sheet->setCellValueByColumnAndRow(
                    $column,
                    $rowNum,
                    $value
                );
            }
        }
    }

    protected function getColumnForField($field)
    {
        // Strip off the last characters: _value
        $field = substr($field, 0, -6);
        $benchmark = $this->getBenchmark($field);

        if (!empty($benchmark['column'])) {
            return $benchmark['column'];
        }
    }

    protected function extractCollegeInfo($row)
    {
        $parts = explode('_', $row['title']);
        $ipeds = array_pop($parts);
        $name = array_pop($parts);

        return array(
            'ipeds' => $ipeds,
            'name' => $name
        );
    }

    protected function getBenchmark($key)
    {
        $benchmarks = $this->getBenchmarks();

        if (!empty($benchmarks[$key])) {
            return $benchmarks[$key];
        }
    }

    protected function getBenchmarks()
    {
        if (empty($this->benchmarks)) {
            $this->benchmarks = $this->getBenchmarksFromDb();
        }

        return $this->benchmarks;
    }

    protected function getBenchmarksFromDb()
    {
        $column = 2;
        $query = "SELECT t.name as formLabel, i.label, i.field_name, i.type_name, weight
FROM content_node_field_instance i
INNER JOIN node_type t ON i.type_name = t.type
WHERE t.name LIKE 'Form%'
AND i.label IS NOT NULL
AND i.label != ''
AND i.field_name != 'field_data_entry_year'
AND i.field_name != 'field_view_results'
ORDER BY CAST(substring_index(substring(i.field_name, 7), '_', 1) AS UNSIGNED), i.weight";
        $results = $this->dbAdapter->query($query, array());

        $benchmarks = array();
        foreach ($results as $row) {
            if (empty($row['field_name'])) {
                continue;
            }

            $row['column'] = $column;
            $benchmarks[$row['field_name']] = $row;

            $column++;
        }

        return $benchmarks;
    }

    protected function download()
    {
        // redirect output to client browser
        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $this->filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }

    public function getTables()
    {
        $tables = array(
            'content_type_group_form1_subscriber_info', // 1
            'content_type_group_form2_student_compl_tsf', // 2
            'content_type_group_form3_stu_perf_transf', // 3
            'content_type_group_form4_cred_stud_enr', // 4
            'content_type_group_form5_stud_satis_eng', // 5
            'content_type_group_form6_stud_goal', // 6
            'content_type_group_form7_col_ret_succ', // 7
            'content_type_group_form8_dev_ret_succ', // 8
            'content_type_group_form9_dev_ret_succ_first_c', // 9
            'content_type_group_form10_career_comp', // 10
            'content_type_group_form11_ret_succ_core', // 11
            'content_type_group_form12_instw_cred_grad', // 12
            'content_type_group_form13a_minority', // 13a
            'content_type_group_form13b_hschool_grads', // 13b
            'content_type_group_form14a_market_pen_stud', // 14a
            'content_type_group_form14b_market_pen_com', // 14b
            'content_type_group_form15_fy_bni', // 15
            'content_type_group_form16a_av_cred_sect', // 16a
            'content_type_group_form16b_cred_co_stud_fac', // 16b
            'content_type_group_form16c_inst_fac_load', // 16c
            'content_type_group_form17a_dist_lear_sec_cred', // 17a
            'content_type_group_form17b_dist_learn_grad', // 17b
            'content_type_group_form18_stud_serv_staff', // 18
            'content_type_group_form19a_ret_dept', // 19a
            'content_type_group_form19b_griev_har', // 19b
            'content_type_group_form20a_cst_crh_fte_stud', // 20a
            'content_type_group_form20b_dev_train_per_empl' // 20b
        );

        return $tables;
    }
}
