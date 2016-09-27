<?php

namespace Mrss\Service;

use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Doctrine\DBAL\Connection;

class ObservationGenerator
{
    protected $study;

    protected $connection;

    protected $observationFile = 'module/Mrss/src/Mrss/Entity/Observation.php';

    protected $stats = array(
        'added' => array(),
        'dropped' => array(),
        'errors' => array()
    );

    public function generate($force = false)
    {
        if ($force || $this->observationEntityNeedsUpdate()) {
            $code = $this->getObservationCode();
            $this->writeCodeToObservation($code);
        }

        $this->updateDatabase();
        $this->clearCaches();
    }

    public function getObservationCode()
    {
        $properties = $this->getProperties();

        $docblock = DocBlockGenerator::fromArray(array(
            'shortDescription' => 'Observation entity class generated by ObservationGenerator',
            'longDescription'  => '',
            'tags' => array(
                array(
                    'name' => 'ORM\Entity',
                ),
                array(
                    'name' => '@ORM\Table(name="observations")',
                ),
            ),
        ));

        $class = new ClassGenerator();
        $class->setName('Observation')
            ->setExtendedClass('ObservationBase')
            ->setNamespaceName('Mrss\Entity')
            ->addUse('Doctrine\ORM\Mapping', 'ORM')
            ->addUse('Mrss\Entity\Exception')
            ->setDocBlock($docblock);


        foreach ($properties as $property) {
            $class->addPropertyFromGenerator($property);
        }

        $code = $class->generate();

        // Opening php tag and strip the ' = null' from properties
        $code = '<?php ' . str_replace(' = null;', ';', $code);

        return $code;
    }

    protected function checkForMissingFields()
    {
        $diff = $this->getMissingFields();

        if ($diff) {
            echo 'Props in database, but not Observation:';
            pr($diff);
        } else {
            echo "Observation appears to be up-to-date already.";
        }
    }

    protected function getMissingFields()
    {
        $observation = new Observation;
        $props = $observation->getAllProperties();
        $props = array_keys($props);

        $dbColumns = array();
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $dbColumns[] = $benchmark->getDbColumn();
        }

        $diff = array_diff($dbColumns, $props);

        return $diff;
    }

    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    protected function getProperties()
    {
        $properties = $this->getBaseProperties();
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $type = $this->getColumnType($benchmark->getInputType());

            $pg = PropertyGenerator::fromArray(array(
                'name' => $benchmark->getDbColumn(),
                'flags' => array(PropertyGenerator::FLAG_PROTECTED),
                'docblock' => array(
                    'tags' => array(
                        array(
                            'name' => 'ORM\Column(type="' . $type . '", nullable=true)',
                        )
                    )
                ),
            ));

            $properties[$benchmark->getDbColumn()] = $pg;
        }

        return $properties;
    }

    protected function getBaseProperties()
    {
        $baseProperties = array();

        // Id
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'id',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\Id',
                    ),
                    array(
                        'name' => 'ORM\GeneratedValue(strategy="AUTO")',
                    ),
                    array(
                        'name' => 'ORM\Column(type="integer")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // Year
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'year',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\Column(type="integer")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // Migrated
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'migrated',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\Column(type="boolean")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // College
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'college',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\ManyToOne(targetEntity="College", inversedBy="observations")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // SubObservations
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'subObservations',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\OneToMany(targetEntity="SubObservation", mappedBy="observation")',
                    ),
                    array(
                        'name' => 'ORM\OrderBy({"id" = "ASC"})',
                    ),
                    array(
                        'name' => 'var SubObservation[]',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;


        // Subscriptions
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'subscriptions',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\OneToMany(targetEntity="Subscription", mappedBy="observation")',
                    ),
                    array(
                        'name' => 'var Subscription[]',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        return $baseProperties;
    }

    /**
     * Get the doctrine entity property type
     *
     * @param $inputType
     * @return string
     */
    protected function getColumnType($inputType)
    {
        $colType = 'float';

        if ($inputType == 'radio' || $inputType == 'text') {
            $colType = 'string';
        } elseif ($inputType == 'textarea') {
            $colType = 'text';
        } elseif ($inputType == 'number') {
            $colType = 'integer';
        }

        return $colType;
    }

    protected function getDatabaseColumnType($inputType)
    {
        $colType = 'DOUBLE PRECISION';

        if ($inputType == 'radio' || $inputType == 'text') {
            $colType = 'VARCHAR(255)';
        } elseif ($inputType == 'textarea') {
            $colType = 'LONGTEXT';
        } elseif ($inputType == 'number') {
            $colType = 'INT(11)';
        }

        return $colType;
    }

    protected function writeCodeToObservation($code)
    {
        file_put_contents($this->observationFile, $code);
    }

    /**
     * Programatically add/drop observation table columns. The change won't have to use migrations.
     */
    protected function updateDatabase()
    {
        // Adds
        foreach ($this->getBenchmarksNotInDatabase() as $benchmark) {
            $this->addColumnForBenchmark($benchmark);
            $this->stats['added'][] = $benchmark->getDbColumn();
        }

        // Drops
        foreach ($this->getDbColumnsNotInBenchmarks() as $dbColumnToDrop) {
            $this->dropColumn($dbColumnToDrop);
            $this->stats['dropped'][] = $dbColumnToDrop;
        }
    }

    protected function getObservationColumns()
    {
        $sql = "DESCRIBE observations";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute();

        $colums = $stmt->fetchAll();

        $dataColumns = array();
        foreach ($colums as $i => $columnInfo) {
            // Skip the first 4 (id, college, year, cipCode)
            if ($i > 3) {
                $dataColumns[] = $columnInfo['Field'];
            }
        }

        return $dataColumns;
    }

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function getAllBenchmarkDbColumns()
    {
        $dbColumns = array();
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $dbColumns[] = $benchmark->getDbColumn();
        }

        // Remove duplicates
        $dbColumns = array_unique($dbColumns);

        return $dbColumns;
    }

    public function getBenchmarksNotInDatabase()
    {
        $databaseColumns = $this->getObservationColumns();

        $newBenchmarks = array();
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            if (!in_array($benchmark->getDbColumn(), $databaseColumns)) {
                $newBenchmarks[] = $benchmark;
            }
        }

        return $newBenchmarks;
    }

    public function getDbColumnsNotInBenchmarks()
    {
        $benchmarkDbColumns = $this->getAllBenchmarkDbColumns();
        $databaseColumns = $this->getObservationColumns();

        $inDatabase = array_diff($databaseColumns, $benchmarkDbColumns);

        return $inDatabase;
    }

    public function addColumnForBenchmark(Benchmark $benchmark)
    {
        $columnType = $this->getDatabaseColumnType($benchmark->getInputType());
        $dbColumn = $benchmark->getDbColumn();

        $sql = "ALTER TABLE observations ADD $dbColumn $columnType DEFAULT NULL";
        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute();
    }

    public function dropColumn($dbColumn)
    {
        $sql = "ALTER TABLE observations DROP $dbColumn";

        $stmt = $this->getConnection()->prepare($sql);

        $result = $stmt->execute();
    }

    public function getStats()
    {
        return $this->stats;
    }

    public function observationEntityNeedsUpdate()
    {
        $missing = $this->getMissingFields();

        return count($missing);
    }

    public function summarizeStats()
    {
        $message = '';

        foreach (array('added', 'dropped') as $action) {
            if ($columns = $this->stats[$action]) {
                $list = implode(', ', $columns);
                $noun = 'column';
                if (count($columns) != 1) {
                    $noun = 'columns';
                }

                $message .= "Database $noun $action: $list. ";
            }
        }

        foreach ($this->stats['errors'] as $error) {
            $message .= $error . " ";
        }

        return $message;
    }

    protected function clearCaches()
    {
        $cacheFiles = array(
            "data/cache/classes.php.cache",
            "data/cache/module-config-cache.config_cache.php",
        );

        $cacheFiles = array_merge($cacheFiles, glob("data/DoctrineModule/cache/*"));

        foreach ($cacheFiles as $file) {
            if ($fullFile = realpath($file)) {
                if (is_dir($fullFile)) {
                    array_map('unlink', glob("$fullFile/*.*"));
                    $result = rmdir($fullFile);
                } else {
                    $result = unlink($fullFile);
                }

                if (!$result) {
                    $this->stats['errors'][] = "Problem clearing cache: $fullFile";
                }
            }
        }

    }
}
