<?php

namespace Mrss\Service;

//use Mrss\Entity\Benchmark;

class VariableSubstitution
{
    protected $studyYear;

    protected $variables;

    /**
     * The text can include variables like [year_minus_2]
     *
     * @param $text
     * @return mixed
     */
    public function substitute($text)
    {
        $subbed = $text;
        foreach ($this->getVariables() as $variable => $value) {
            $subbed = str_replace('[' . $variable . ']', $value, $subbed);
        }

        return $subbed;
    }

    public function setStudyYear($year)
    {
        $this->studyYear = $year;

        return $this;
    }

    public function getStudyYear()
    {
        return $this->studyYear;
    }

    public function getVariables()
    {
        if (!count($this->variables)) {
            $range = range(-7, 2);
            $variables = array('year' => $this->getStudyYear());
            foreach ($range as $offset) {
                $value = $this->getStudyYear() + $offset;
                if ($offset > 0) {
                    $action = 'plus';
                } else {
                    $action = 'minus';
                }
                $variable = 'year_' . $action . '_' . abs($offset);

                $variables[$variable] = $value;
            }

            $this->variables = $variables;
        }


        return $this->variables;
    }
}
