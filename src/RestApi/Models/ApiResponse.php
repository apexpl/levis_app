<?php
declare(strict_types = 1);

namespace Levis\App\RestApi\Models;

use Nyholm\Psr7\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

/**
 * API Request
 */
class ApiResponse
{

    /**
     * Constructor
     */
    public function __construct(
        private int $status = 200, 
        private array $data = [], 
        private string $message = '', 
        private float $version = 1.0
    ) { 

    }

    /**
     * Get response
     */
    public function get():ResponseInterface
    {

        // Set JSON array
        $json = json_encode([
            'status' => str_starts_with((string) $this->status, '2') ? 'ok' : 'error', 
            'version' => $this->version, 
            'message' => $this->message, 
            'data' => $this->data
        ]);

    // Create response
        $res = (new Response(status: $this->status, body: $json))
            ->withStatus($this->status) 
            ->withAddedHeader('Content-type', 'application/json');

        // Return
        return $res;
    }

}


