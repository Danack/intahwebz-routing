<?php

namespace Intahwebz\Routing\Example\Security;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\AclInterface;


class AccessControl implements AclInterface {
    
    private $acl = null;
    
    private $roles = array();
    
    public function __construct() {
        $this->acl = new Acl();

        $this->roles[Role::ANONYMOUS] = new GenericRole(Role::ANONYMOUS);
        $this->roles[Role::ADMIN] = new GenericRole(Role::ADMIN);

        $this->acl->addRole($this->roles[Role::ADMIN]);
        $this->acl->addRole($this->roles[Role::ANONYMOUS]);

        $this->acl->addResource(Resource::ADMIN);
        $this->acl->addResource(Resource::CONTENT);

        //Null roles = all roles.
        $this->acl->allow(null, Resource::CONTENT, Privilege::VIEW);

        //Null privilege = all 
        //$this->acl->allow(Role::ADMIN, Resource::CONTENT);
        $this->acl->allow(Role::ADMIN, Resource::ADMIN);
        //$this->acl->allow(Role::ADMIN, Resource::ADMIN, Privilege::VIEW);
    }

    public function isAllowed($userRole = null, $resource = null, $privilege = null){

        if ($userRole == null) {
            $userRole = Role::ANONYMOUS;
        }

        if ($resource == null) {
            $resource = Resource::CONTENT;
        }

        if ($privilege == null) {
            $privilege = Privilege::UNLISTED;
        }

        return $this->acl->isAllowed($userRole, $resource, $privilege);
    }

    /**
     * Returns true if and only if the Resource exists in the ACL
     *
     * The $resource parameter can either be a Resource or a Resource identifier.
     *
     * @param  \Zend\Permissions\Acl\Resource\ResourceInterface|string $resource
     * @return bool
     */
    public function hasResource($resource) {
        // TODO: Implement hasResource() method.
        return false;
    }
}
