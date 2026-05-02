<?php

namespace WPStaging\Pro;

class WPStagingRestEndpoint
{
    /**
     * @param string $message
     * @param int $code
     * @param array $data
     * @return \WP_Error|\WP_REST_Response
     */
    protected function successResponse(string $message, int $code = 200, array $data = [])
    {
        return rest_ensure_response([
            'success' => true,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
        ]);
    }

    /**
     * @param string $message
     * @param int $code
     * @param array $data
     * @return \WP_Error|\WP_REST_Response
     */
    protected function errorResponse(string $message, int $code = 500, array $data = [])
    {
        return rest_ensure_response([
            'success' => false,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
        ]);
    }
}
