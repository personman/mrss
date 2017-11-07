<?php

namespace Mrss\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use \Mrss\Entity\User;

class PublishCustomReport extends AbstractForm
{
    /** @var User $user */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        // Call the parent constructor
        parent::__construct('publish_report');

        $this->addBasicFields();
        $this->addPeerGroupTarget();
        $this->addPeerGroupGroup();

        $this->add($this->getButtonFieldset());


        $this->setInputFilter($this->getInputFilterSetup());
    }

    protected function addBasicFields()
    {
        $this->addId();
    }

    protected function addPeerGroupTarget()
    {
        $this->add(
            array(
                'name' => 'target',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Peer Group to Publish To',
                    'empty_option' => 'Select a peer group',
                    'help-block' => 'Each user at each member of the peer group will get a copy of the report.'
                ),
                'attributes' => array(
                    'id' => 'target',
                    'options' => $this->getPeerGroups()
                )
            )
        );
    }

    protected function addPeerGroupGroup()
    {
        $this->add(
            array(
                'name' => 'group',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Peer Group to Include in the Report',
                    'empty_option' => 'Select a peer group',
                    'help-block' => 'If this is left blank, the published report will not include any peer group aggregate data. If you select one of your peer groups, the recipients of the report will get a copy of that peer group. Any peer groups used in report items are replaced with references to the user\'s copy of that peer group. If they edit that peer group and then refresh the report, they will see the updated peer group.'
                ),
                'attributes' => array(
                    'id' => 'group',
                    'options' => $this->getPeerGroups()
                )
            )
        );
    }

    protected function getPeerGroups()
    {
        $peerGroups = $this->user->getPeerGroups();

        $names = array();
        foreach ($peerGroups as $peerGroup) {
            $names[$peerGroup->getId()] = $peerGroup->getName();
        }

        return $names;
    }

    public function getInputFilterSetup()
    {
        $inputFilter = new InputFilter();

        $input = new Input('group');
        $input->setRequired(false);
        $input->setAllowEmpty(true);
        $inputFilter->add($input);

        return $inputFilter;
    }
}
