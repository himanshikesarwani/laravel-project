<?php
namespace App\Modules\User\Repositories\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Entities\User;
use App\Repositories\BaseRepository;
use Auth;

class UserRepository extends BaseRepository implements UserInterface
{

    /*
     * |--------------------------------------------------------------------------
     * | UserRepository
     * |--------------------------------------------------------------------------
     * | Our user repository, containing commonly used queries
     * |
     */

    /**
     *
     * @var $user
     */
    protected $user;

    /**
     *
     * @var $userData
     */
    protected $userData;

    /**
     * Function to construct user object
     *
     * @param Model $user
     */
    public function __construct(Model $user)
    {
        parent::__construct($user);
        $this->user = $user;
    }

    /**
     * Function to delete an existing model object
     *
     * @param array $conditions
     * @return void
     */
    public function delete(array $conditions)
    {

    }

    /**
     * Function to authenticate user
     *
     * @param array $userArray
     * @return object $result
     */
    public function userAuthenticate(array $userArray)
    {
        try {
            // send curl request for post
            $email = $userArray['email'];
            $password = $userArray['password'];
            $result = Auth::attempt([
                'email'  => strtolower($email),
                'password'  => $password 
            ]);
            if($result){
                $result = $this->checkUserEmailExistence($email);
                return $result;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to check the existence of email in the system
     *
     * @param string $email
     *
     * @return boolean
     */
    public function checkUserEmailExistence($email)
    {
        return $this->user->where('email', '=', $email)->first();
    }


    /**
     * Function to get user by ites Id
     *
     * @param Integer $userId
     */
    public function getUserById($userId)
    {
        try {
            return $this->user->find($userId);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

}
