<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chart
 *
 * Configuration for a chart
 *
 * @ORM\Entity
 * @ORM\Table(name="report_items")
 */
class ReportItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $subtitle;

    /**
     * @ORM\ManyToOne(targetEntity="Report")
     * @ORM\JoinColumn(
     * name="report_id",
     * referencedColumnName="id",
     * onDelete="CASCADE",
     * nullable=false
     * )
     */
    protected $report;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="College")
     * @ORM\JoinColumn(
     * name="highlighted_college_id",
     * referencedColumnName="id",
     * onDelete="CASCADE",
     * nullable=true
     * )
     */
    protected $highlightedCollege;

    /**
     * A JSON array
     * @ORM\Column(type="text", nullable=true)
     */
    protected $config;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark1_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $benchmark1;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark2_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $benchmark2;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark3_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $benchmark3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $year;

    /**
     * A JSON array caching the chart config and footnotes
     * @ORM\Column(type="text", nullable=true)
     */
    protected $cache;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sequence;


    /**
     * @param mixed $benchmark1
     */
    public function setBenchmark1($benchmark1)
    {
        $this->benchmark1 = $benchmark1;
    }

    /**
     * @return mixed
     */
    public function getBenchmark1()
    {
        return $this->benchmark1;
    }

    /**
     * @param mixed $benchmark2
     */
    public function setBenchmark2($benchmark2)
    {
        $this->benchmark2 = $benchmark2;
    }

    /**
     * @return mixed
     */
    public function getBenchmark2()
    {
        return $this->benchmark2;
    }

    /**
     * @param mixed $benchmark3
     */
    public function setBenchmark3($benchmark3)
    {
        $this->benchmark3 = $benchmark3;
    }

    /**
     * @return mixed
     */
    public function getBenchmark3()
    {
        return $this->benchmark3;
    }

    /**
     * @param mixed $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return mixed
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        if (is_array($config)) {
            $config = json_encode($config);
        }

        $this->config = $config;
    }

    /**
     * @param bool $decodeJson
     * @return mixed
     */
    public function getConfig($decodeJson = true)
    {
        $config = $this->config;

        if ($decodeJson) {
            $config = json_decode($config, true);
        }

        return $config;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $highlightedCollege
     */
    public function setHighlightedCollege($highlightedCollege)
    {
        $this->highlightedCollege = $highlightedCollege;
    }

    /**
     * @return mixed
     */
    public function getHighlightedCollege()
    {
        return $this->highlightedCollege;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @return mixed
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    public function setCache($cache)
    {
        if (is_array($cache)) {
            $cache = json_encode($cache);
        }

        $this->cache = $cache;

        return $this;
    }

    public function getCache($decode = false)
    {
        $cache = $this->cache;
        if ($decode) {
            $cache = json_decode($cache, true);
        }

        return $cache;
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function getCacheChart()
    {
        $cache = $this->getCache(true);
        $chart = null;
        if (!empty($cache['chart'])) {
            $chart = $cache['chart'];
        }

        return $chart;
    }

    public function setCacheChart($chart)
    {
        $cache = $this->getCache(true);
        $cache['chart'] = $chart;
        $this->setCache($cache);

        return $this;
    }

    public function getCacheFootnotes()
    {
        $cache = $this->getCache(true);
        $footnotes = null;
        if (!empty($cache['footnotes'])) {
            $footnotes = $cache['footnotes'];
        }

        return $footnotes;
    }

    public function isText()
    {
        $config = $this->getConfig();
        $isText = ($config['presentation'] == 'text');

        return $isText;
    }

    public function getText()
    {
        $config = $this->getConfig();

        return $config['content'];
    }

    public function getWidth()
    {
        $config = $this->getConfig(true);
        $width = 'half';
        if (!empty($config['width']) && $config['width'] == 'full') {
            $width = 'full';
        }

        return $width;
    }

    public function getWrapperClass()
    {
        $class = 'complete-chart-box';
        if ($this->getWidth() == 'full') {
            $class = 'complete-chart-box full-width';
        }

        return $class;
    }
}
