<?php

namespace Mrss\Service;

use Zend\Dom\Query;

/**
 * Class ImportNccwtp
 *
 * This is no longer in use and only included for historical or analysis purposes
 * This class just extracts some data from the old WTP html forms
 *
 * @deprecated
 * @package Mrss\Service
 */

class ImportNccwtp
{
    protected $studyId = 3;
    protected $importDirectory = 'data/NCCWTP';

    /** @var Query */
    protected $dom;

    public function import()
    {
        foreach ($this->getFiles() as $filename) {
            if (file_exists($filename)) {
                $this->dom = new Query(file_get_contents($filename));

                $groupName = $this->getTitle();
                //var_dump($groupName);

                foreach ($this->getBenchmarks() as $benchmark) {
                    //var_dump($benchmark);
                }

            }
        }
    }

    public function getFiles()
    {
        $files = scandir($this->importDirectory);

        $filesWithPath = array();
        foreach ($files as $filename) {
            if (!in_array($filename, array('.', '..'))) {
                $filesWithPath[] = $this->importDirectory . '/' . $filename;
            }
        }

        return $filesWithPath;
    }

    public function getTitle()
    {
        $result = $this->dom->execute('title');
        foreach ($result as $element) {
            $title = $element->textContent;
            $title = str_replace(' | NCCWTP', '', $title);
        }

        return $title;
    }

    public function getBenchmarks()
    {
        $result = $this->dom->execute('form div.row');
        $benchmarks = array();
        foreach ($result as $row) {
            $benchmarkHtml = $this->getInnerHTML($row);
            $benchmarkDom = new Query($benchmarkHtml);
            $benchmark = array();

            $benchmark['name'] = $this->getName($benchmarkDom);
            $benchmark['dbColumn'] = $this->getDbColumn($benchmarkDom);


            $benchmarks[] = $benchmark;
        }

        //var_dump($benchmarks);

        return array();
    }

    public function getName($benchmarkDom)
    {
        $toReplace = ' (for the most recent completed fiscal year)';

        $name = '';
        $result = $benchmarkDom->execute('.span8');
        foreach ($result as $node) {
            $name = trim($node->textContent);
            $name = str_replace($toReplace, '', $name);
        }

        return $name;
    }

    public function getDbColumn($benchmarkDom)
    {
        $result = $benchmarkDom->execute('input.span2');
        $dbColumn = '';
        foreach ($result as $node) {
            $dbColumn = $node->getAttribute('name');
        }

        return $dbColumn;
    }

    public function getInnerHTML($Node)
    {
        $Document = new \DOMDocument();
        $Document->appendChild($Document->importNode($Node, true));
        return $Document->saveHTML();
    }
}
