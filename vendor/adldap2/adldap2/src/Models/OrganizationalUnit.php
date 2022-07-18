<?php

namespace Adldap\Models;

/**
 * Class OrganizationalUnit.
 *
 * Represents an LDAP organizational unit.
 */
class OrganizationalUnit extends Entry
{
    use Concerns\HasDescription;

    /**
     * {@inheritdoc}
     */
    protected function getCreatableDn()
    {
        return $this->getDnBuilder()->addOU($this->getOu());
    }

    /**
     * Retrieves the organization units OU attribute.
     *
     * @return string
     */
    public function getOu()
    {
        return $this->getFirstAttribute($this->schema->organizationalUnitShort());
    }
}
