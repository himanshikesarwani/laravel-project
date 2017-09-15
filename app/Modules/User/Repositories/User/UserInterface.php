<?php
namespace App\Modules\User\Repositories\User;

use App\Entities\User;

interface UserInterface
{

    /**
     * Function to check the existence of email in the system
     *
     * @param string $email
     */
    public function checkUserEmailExistence($email);


}
