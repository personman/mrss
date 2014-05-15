<?php

namespace Mrss\Model;

use Mrss\Entity\SubscriptionDraft as SubscriptionDraftEntity;

/**
 * Class SubscriptionDraft
 *
 * For storing draft subscriptions
 *
 * @package Mrss\Model
 */
class SubscriptionDraft extends AbstractModel
{
    protected $entity = 'Mrss\Entity\SubscriptionDraft';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function save(SubscriptionDraftEntity $form)
    {
        $this->getEntityManager()->persist($form);

        // Flush here or leave it to some other code?
    }

    public function delete(SubscriptionDraftEntity $form)
    {
        $this->getEntityManager()->remove($form);
    }
}
