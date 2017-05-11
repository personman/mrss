<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilterAwareInterface;

/**
 * Class Structure
 *
 * @ORM\Entity
 * @ORM\Table(name="structures")

 */
class Structure implements FormFieldsetProviderInterface//, InputFilterAwareInterface
{
    protected $page = null;

    protected $benchmarks = array();

    protected $children = array();

    protected $benchmarkModel;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;


    /** @ORM\Column(type="text", nullable=true) */
    protected $json = null;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Structure
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        if (empty($this->json)) {
            $this->json = '[]';
        }

        return $this->json;
    }

    /**
     * @param mixed $json
     * @return Structure
     */
    public function setJson($json)
    {
        $this->json = $json;
        return $this;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPageStructure()
    {
        $structure = $this->getStructureArray();
        $currentPage = $this->getPage();

        foreach ($structure as $page) {
            if (!empty($page['url']) && $page['url'] == $currentPage) {
                return $page;
            }
        }
    }

    protected function getStructureArray()
    {
        $structure = $this->getJson();
        $structure = json_decode($structure, true);

        return $structure;
    }

    public function getPages()
    {
        $pageStructure = $this->getStructureArray();
        $pages = array();

        foreach ($pageStructure as $page) {
            $pageUrl = $page['url'];
            $this->setPage($pageUrl);
            $pages[] = $this->getBenchmarkGroup();
        }

        return $pages;
    }

    public function getBenchmarkGroup()
    {
        $group = clone $this;
        return $group;
    }

    /**
     * Skip benchmarks that don't work for this year and that are computed
     * @param $year
     * @return array
     */
    public function getElements($year)
    {
        $benchmarks = $this->getAllBenchmarks();

        $nonComputed = array();
        foreach ($benchmarks as $benchmark) {
            if (!$benchmark->getComputed() && $benchmark->isAvailableForYear($year)) {
                $nonComputed[] = $benchmark;
            }
        }

        return $nonComputed;
    }

    public function getUseSubObservation()
    {
        return false;
    }

    public function getLabel()
    {
        $pageStructure = $this->getPageStructure();
        return $pageStructure['name'];
    }

    public function getUrl()
    {
        return $this->getPage();
    }

    public function getName()
    {
        return $this->getLabel();
    }

    // These are for debugging only
    public function getIncompleteBenchmarksForObservation($observation)
    {
        return 0;
    }

    public function getCompleteBenchmarksForObservation($observation)
    {
        return 0;
    }

    public function getTimeframe()
    {
        return null;
    }

    public function getCompletionPercentageForObservation($observation)
    {
        $debug = true;

        $benchmarks = $this->getAllBenchmarks();
        $total = count($benchmarks);

        $populated = 0;
        foreach ($benchmarks as $benchmark) {
            $value = $observation->get($benchmark->getDbColumn());

            if (!is_null($value)) {
                $populated++;
            }
        }

        $completion = 0;
        if ($total) {
            $completion = $populated / $total * 100;
            if ($debug) {
            }
        }

        return $completion;
    }

    public function getFormat()
    {
        return 'one-col';
    }

    public function getShortName()
    {
        return $this->getPage();
    }

    public function getDescription()
    {
        return '';
    }

    public function getChildren($year = null, $includeComputed = true)
    {
        $structure = array($this->getPageStructure());


        // Recursive
        $this->loadChildren($structure, $year, $includeComputed);

        return $this->children;
    }

    public function loadChildren($structure, $year, $includeComputed)
    {
        $sequence = 0;
        foreach ($structure as $child) {
            if ($this->childIsHeading($child)) {
                $item = new BenchmarkHeading();
                $item->setName($child['name']);
                $item->setSequence($sequence++);

                $this->children[] = $item;
            } elseif ($this->childIsBenchmark($child)) {
                $item = $this->getBenchmark($child['benchmark']);

                if (($year === null || $item->isAvailableForYear($year)) && ($includeComputed || !$item->getComputed())) {
                    $this->children[] = $item;
                }
            }

            // Recurse:
            if (!empty($child['children'])) {
                $this->loadChildren($child['children'], $year, $includeComputed);
            }

        }
    }

    protected function childIsHeading($child)
    {
        return (!empty($child['name']) && empty($child['benchmark']) && empty($child['url']));
    }

    protected function childIsBenchmark($child)
    {
        return (!empty($child['benchmark']) && empty($child['url']));
    }

    /**
     * @return \Mrss\Entity\Benchmark[]
     */
    protected function getAllBenchmarks()
    {
        $pageStructure = $this->getPageStructure();
        $this->benchmarks = array();

        if (empty($pageStructure)) {
            $pageStructure = $this->getStructureArray();
        } else {
            $pageStructure = array($pageStructure);
        }

        // Recursive:
        $this->getBenchmarks($pageStructure);

        return $this->benchmarks;
    }

    protected function getBenchmarks($structureArray)
    {
        foreach ($structureArray as $child) {

            if ($this->childIsBenchmark($child)) {
                $benchmarkId = $child['benchmark'];
                $this->benchmarks[] = $this->getBenchmark($benchmarkId);

            } elseif (!empty($child['children'])) {
                $this->getBenchmarks($child['children']);
            }
        }
    }

    protected function getBenchmark($benchmarkId)
    {
        return $this->getBenchmarkModel()->find($benchmarkId);
    }

    public function getBenchmarksForYear($year)
    {
        $benchmarksForYear = array();
        foreach ($this->getAllBenchmarks() as $benchmark) {
            if ($benchmark->isAvailableForYear($year)) {
                $benchmarksForYear[] = $benchmark;
            }
        }

        return $benchmarksForYear;
    }

    public function getBenchmarkIdsForYear($year)
    {
        $benchmarkIds = array();
        foreach ($this->getBenchmarksForYear($year) as $benchmark) {
            $benchmarkIds[] = $benchmark->getId();
        }

        return $benchmarkIds;
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }
}
