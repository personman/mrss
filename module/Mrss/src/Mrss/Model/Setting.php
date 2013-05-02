<?php

namespace Mrss\Model;

use \Mrss\Entity\Setting as SettingEntity;

/**
 * Class Setting
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Setting extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Setting';

    public function setValueForIdentifier($identifier, $value)
    {
        $setting = $this->findOneByIdentifier($identifier);

        if (empty($setting)) {
            $setting = new $this->entity;
            $setting->setIdentifier($identifier);
        }

        $setting->setValue($value);

        $this->save($setting);
        $this->getEntityManager()->flush();
    }

    public function getValueForIdentifier($identifier)
    {
        $setting = $this->findOneByIdentifier($identifier);

        if (empty($setting)) {
            $value = null;
        } else {
            $value = $setting->getValue();
        }

        return $value;
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->getRepository()->findOneBy(array('identifier' => $identifier));
    }

    public function save(SettingEntity $setting)
    {
        $this->getEntityManager()->persist($setting);
    }
}
