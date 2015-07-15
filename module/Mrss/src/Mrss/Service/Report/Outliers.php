<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use Mrss\Entity\Outlier;
use Mrss\Entity\College;
use Mrss\Entity\Benchmark;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\View\Renderer\RendererInterface;

class Outliers extends Report
{
    public function calculateOutliersForYear($year)
    {
        $this->calculateAllComputedFields($year);

        $stats = array(
            'high' => 0,
            'low' => 0,
            'missing' => 0
        );

        $start = microtime(1);

        $calculator = $this->getCalculator();

        // Clear any existing outliers for the year/study
        $studyId = $this->getStudy()->getId();
        $this->getOutlierModel()
            ->deleteByStudyAndYear($studyId, $year);
        $this->getOutlierModel()->getEntityManager()->flush();

        // Loop over the benchmarks
        foreach ($this->getStudy()->getBenchmarksForYear($year) as $benchmark) {
            /** @var Benchmark $benchmark */

            // Skip over computed benchmarks
            /*if ($benchmark->getComputed()) {
                continue;
            }*/

            // Get the data for all subscribers (skip nulls)
            $data = $this->collectDataForBenchmark($benchmark, $year);

            // If there's no data, move on
            if (empty($data)) {
                continue;
            }

            $calculator->setData($data);

            // Here's the key bit, where the outliers are actually calculated
            $outliers = $calculator->getOutliers();

            // Now save them
            foreach ($outliers as $outlierInfo) {
                $outlier = new Outlier;
                $outlier->setValue($outlierInfo['value']);
                $outlier->setBenchmark($benchmark);
                $outlier->setStudy($this->getStudy());
                $outlier->setYear($year);
                $problem = $outlierInfo['problem'];
                $outlier->setProblem($problem);
                $college = $this->getOutlierModel()->getEntityManager()
                    ->getReference('Mrss\Entity\College', $outlierInfo['college']);
                $outlier->setCollege($college);

                $this->getOutlierModel()->save($outlier);

                // Some stats
                $stats[$problem]++;
            }

            // Handle missing outliers
            if ($benchmark->getRequired()) {
                $data = $this->collectDataForBenchmark($benchmark, $year, false);

                foreach ($data as $collegeId => $datum) {
                    if ($datum === null) {
                        $outlier = new Outlier;
                        $outlier->setBenchmark($benchmark);
                        $outlier->setStudy($this->getStudy());
                        $outlier->setYear($year);
                        $problem = 'missing';
                        $outlier->setProblem($problem);
                        $college = $this->getOutlierModel()->getEntityManager()
                            ->getReference('Mrss\Entity\College', $collegeId);
                        $outlier->setCollege($college);

                        $this->getOutlierModel()->save($outlier);

                        // Some stats
                        $stats[$problem]++;
                    }
                }
            }
        }

        // Save the new report calculation date
        $settingKey = $this->getOutliersCalculatedSettingKey($year);
        $this->getSettingModel()->setValueForIdentifier($settingKey, date('c'));
        $this->getSettingModel()->getEntityManager()->flush();

        // Timer
        $end = microtime(1);
        $stats['time'] = round($end - $start, 1) . ' seconds';

        return $stats;
    }

    /**
     * Get the outliers for the study/year, grouped by college
     */
    public function getAdminOutlierReport($excludeNonReported = true)
    {
        $report = array();
        $study = $this->getStudy();
        $year = $study->getCurrentYear();

        // Get colleges subscribed to the study for the year
        $colleges = $this->getCollegeModel()->findByStudyAndYear(
            $study,
            $year
        );

        foreach ($colleges as $college) {
            // Skip
            if (in_array($college->getId(), $this->getExcludedCollegeIds())) {
                continue;
            }

            $outliers = $this->getOutlierModel()
                ->findByCollegeStudyAndYear($college, $study, $year);

            if ($excludeNonReported) {
                $outliers = $this->removeNonReportedOutliers($outliers);
            }

            $observation = $this->getSubscriptionModel()->findOne($year, $college->getId(), $study)->getObservation();
            $this->setObservation($observation);

            $outliers = $this->prepareOutlierRows($outliers);

            $report[] = array(
                'college' => $college,
                'outliers' => $outliers
            );
        }

        return $report;
    }

    /**
     * @param Outlier[] $outliers
     * @return Outlier[]
     */
    public function removeComputedOutliers($outliers)
    {
        $newList = array();

        foreach ($outliers as $outlier) {
            if (!$outlier->getBenchmark()->getComputed()) {
                $newList[] = $outlier;
            }
        }

        return $newList;
    }

    /**
     * @param Outlier[] $outliers
     * @return array
     */
    protected function prepareOutlierRows($outliers)
    {
        $newOutliers = array();
        foreach ($outliers as $outlier) {
            $benchmark = $outlier->getBenchmark();

            if (!$benchmark->getIncludeInNationalReport()) {
                continue;
            }

            $value = $outlier->getValue();
            if ($value !== null) {
                $value = $benchmark->getPrefix() . number_format($value) . $benchmark->getSuffix();
            }

            $newOutliers[] = array(
                'benchmark' => $benchmark->getDescriptiveReportLabel(),
                'computed' => $benchmark->getComputed(),
                'value' => $value,
                'problem' => $outlier->getProblem(),
                'benchmarkGroupId' => $benchmark->getBenchmarkGroup()->getUrl(),
                'dbColumn' => $benchmark->getDbColumn(),
                'equation' => $this->getEquation($benchmark),
                'baseBenchmarks' => $this->getBaseBenchmarks($benchmark)
            );
        }

        return $newOutliers;
    }

    /**
     * @param Outlier[] $outliers
     * @return Outlier[]
     */
    public function removeNonReportedOutliers($outliers)
    {
        $newList = array();

        foreach ($outliers as $outlier) {
            // Exclude max demographics
            if ($outlier->getBenchmark()->getBenchmarkGroup()->getId() == 40) {
                continue;
            }

            if ($outlier->getBenchmark()->getIncludeInNationalReport()) {
                $newList[] = $outlier;
            }
        }

        return $newList;
    }

    public function getBaseBenchmarks(Benchmark $benchmark)
    {
        $benchmarks = array();
        if ($benchmark->getComputed() && $equation = $benchmark->getEquation()) {
            // Expand the equation
            $year = $this->getStudy()->getCurrentYear();
            $equation = $this->getComputedFieldsService()->nestComputedEquations($equation, $year);
            $variables = $this->getComputedFieldsService()->getVariables($equation);

            foreach ($variables as $dbColumn) {
                $baseBenchmark = $this->getBenchmark($dbColumn);

                $value = null;
                if ($this->getObservation()) {
                    $value = $baseBenchmark->format($this->getObservation()->get($dbColumn));
                }
                $benchmarks[] = array(
                    'dbColumn' => $dbColumn,
                    'benchmark' => $baseBenchmark->getDescriptiveReportLabel(),
                    'benchmarkGroup' => $baseBenchmark->getBenchmarkGroup()->getUrl(),
                    'value' => $value
                );
            }
        }

        return $benchmarks;
    }

    public function getEquation(Benchmark $benchmark)
    {
        $nested = true;

        $equation = null;
        if ($benchmark->getComputed()) {
            //$equation = $this->getComputedFieldsService()->getEquationWithLabels($benchmark, $nested);

            if ($observation = $this->getObservation()) {
                $equation = $this->getComputedFieldsService()
                        ->getEquationWithNumbers($benchmark, $this->getObservation(), $nested);
            }
        }

        return $equation;
    }

    public function getOutlierReport(College $college)
    {
        $report = array();
        $study = $this->getStudy();
        $year = $study->getCurrentYear();

        $observation = $this->getSubscriptionModel()->findOne($year, $college->getId(), $study)->getObservation();
        $this->setObservation($observation);


        $outliers = $this->getOutlierModel()
            ->findByCollegeStudyAndYear($college, $study, $year);
        $report[] = array(
            'college' => $college,
            'outliers' => $this->prepareOutlierRows($outliers)
        );

        return $report;
    }

    public function emailOutliers(RendererInterface $renderer, $reallySend = true)
    {
        // For debugging:
        $devOnly = false;


        $reports = $this->getAdminOutlierReport();
        $stats = array('emails' => 0, 'preview' => '');

        // Loop over the admin report in order to send an email to each college
        foreach ($reports as $report) {
            /** @var \Mrss\Entity\College $college */
            $college = $report['college'];

            // Skip
            if (in_array($college->getId(), $this->getExcludedCollegeIds())) {
                continue;
            }

            /** @var \Mrss\Entity\Outlier[] $outliers */
            $outliers = $report['outliers'];

            if ($devOnly && empty($outliers[0]['baseBenchmarks'])) {
                continue;
            }

            $studyName = $this->getStudy()->getDescription();
            $collegeName = $college->getName();
            $year = $this->getStudy()->getCurrentYear();



            $deadline = "July 29, " . date('Y');
            $replyTo = "michelletaylor@jccc.edu";
            $replyToName = "Michelle Taylor";
            $replyToPhone = "(913) 469-3831";
            $url = "nccbp.org";

            //$replyTo = "louguthrie@jccc.edu";
            //$replyTo = "dfergu15@jccc.edu";
            //$replyToName = "Lou Guthrie";
            //$replyToPhone = "(913) 469-8500 x4019";
            //$deadline = "July 10, " . date('Y');
            //$url = "maximizingresources.org";

            $viewParams = array(
                'year' => $year,
                'studyName' => $studyName,
                'url' => $url,
                'deadline' => $deadline,
                'replyTo' => $replyTo,
                'replyToName' => $replyToName,
                'replyToPhone' => $replyToPhone,
                'outliers' => $outliers
            );

            // Select a view for the email body
            if (!empty($outliers)) {
                $view = 'mrss/report/outliers.email.phtml';

            } else {
                $view = 'mrss/report/outliers.email.none.phtml';
            }

            // Build the email body with the view
            $body = $renderer->render($view, $viewParams);

            // Email subject
            $subject = "Outlier report for $studyName";

            // Mime object for html body:
            $html = new MimePart($body);
            $html->type = 'text/html';

            $bodyPart = new MimeMessage;
            $bodyPart->setParts(array($html));

            $message = new Message;

            $message->setSubject($subject);
            $message->setBody($bodyPart);
            $message->addBcc('dfergu15@jccc.edu');
            $message->addFrom($replyTo, $replyToName);
            $message->setReplyTo($replyTo);


            if ($reallySend) {
                $message->addBcc($replyTo);

                // Get recipients
                if (!$devOnly) {
                    foreach ($college->getUsersByStudy($this->getStudy()) as $user) {
                        // @todo: make this more dynamic
                        if ($college->getIpeds() == '155210'
                            && $user->getEmail() != 'jhoyer@jccc.edu') {
                            continue;
                        }


                        $message->addTo($user->getEmail(), $user->getFullName());
                    }
                }


                // Send it:
                $this->getMailTransport()->send($message);

                if ($devOnly) {
                    break;
                }
            } else {
                $to = array();
                foreach ($college->getUsers() as $user) {
                    // @todo: make this more dynamic
                    if ($college->getIpeds() == '155210'
                        && $user->getEmail() != 'jhoyer@jccc.edu') {
                        continue;
                    }

                    $to[] = $user->getEmail();// . '<' . $user->getFullName() . '>';
                }
                $to = implode(', ', $to);

                $stats['preview'] .= "Institution: $collegeName<br>\nTo: $to<br>\n<br>\n$body\n<hr><br>";
            }

            $stats['emails']++;
        }

        return $stats;
    }

    public function getExcludedCollegeIds()
    {
        // Don't email these colleges outlier reports (applies to NCCBP 2015)
        return array(
            1121, // Henry Ford
            437, // Wyoming CCC
            1116, // Vance-Granville
        );
    }
}
