<?php

namespace App\Ldap;

use LdapRecord\Models\Model;
use LdapRecord\Models\Concerns\CanAuthenticate;
use Illuminate\Contracts\Auth\Authenticatable;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;


class AlphaUser extends LdapUser implements Authenticatable
{
    
    /**
     * The object classes of the LDAP model.
     */

    /**
     * The connection name for the LDAP model.
     *
     * @var string|null
     */
   # protected $connection = 'alpha';
    protected ?string $connection = 'alpha';

    protected string $guidKey = 'uuid';

    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    /**
     * Get the attribute key for the LDAP object's email address.
     *
     * @return string
     */
    public function getEmailAttributeKey(): string
    {
        return 'mail';
    }

     /**
     * Get the distinguished name attribute key.
     *
     * @return string
     */
    public function getDnAttributeKey(): string
    {
        return 'distinguishedname';
    }

    /**
     * Get the attribute key for the LDAP object's username.
     *
     * @return string
     */
    public function getUsernameAttributeKey(): string
    {
        return 'samaccountname';
    }

   

}
