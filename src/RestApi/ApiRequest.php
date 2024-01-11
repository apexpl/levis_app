<?php
declare(strict_types = 1);

namespace Levis\App\RestApi;

use Apex\Svc\{App, Di};
use App\RestApi\Models\ApiResponse;


/**
 * API Request helpder
 */
class ApiRequest
{

    // Properties
    protected ?ApiResponse $response = null;

    /**
     * Check required
     */
    public function checkRequired(...$params):bool
    {

        // Initialize
        $app = Di::get(App::class);

        // GO through params
        foreach ($params as $param) { 

            if ( (!$app->hasPost($param)) || ($app->post($param) == '') ) { 
                $this->response = new ApiResponse(400, [], "The parameter '$param' is missing, and is required.");
                return false;
            }
        }

        // Return
        return true;
    }

    /**
     * Get response
     */
    public function getResponse():?ApiResponse
    {
        return $this->response;
    }

}




